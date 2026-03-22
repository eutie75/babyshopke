<?php
declare(strict_types=1);

/**
 * MpesaController
 * ---------------
 * Handles all Daraja API interactions:
 *   - Generating access tokens
 *   - Initiating STK Push
 *   - Querying STK Push status
 *   - Saving/updating mpesa_transactions in DB
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mpesa.php';

class MpesaController
{
    // ── Token ────────────────────────────────────────────────────────────────

    /**
     * Fetch a fresh OAuth2 access token from Daraja.
     * Returns the token string or throws on failure.
     */
    public static function getAccessToken(): string
    {
        $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

        $ch = curl_init(MPESA_AUTH_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => MPESA_ENV === 'production',
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException('M-Pesa auth cURL error: ' . $curlError);
        }
        if ($httpCode !== 200) {
            throw new RuntimeException('M-Pesa auth failed (HTTP ' . $httpCode . '): ' . $response);
        }

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new RuntimeException('M-Pesa: no access_token in auth response.');
        }

        return (string)$data['access_token'];
    }

    // ── STK Push ─────────────────────────────────────────────────────────────

    /**
     * Send an STK Push prompt to the customer's phone.
     *
     * @param  int    $orderId   The order to link this transaction to
     * @param  string $phone     Customer phone in format 2547XXXXXXXX
     * @param  float  $amount    Amount in KES (rounded to integer for M-Pesa)
     * @param  string $accountRef Short reference shown on phone (e.g. "Order #42")
     * @return array  ['checkout_request_id' => ..., 'merchant_request_id' => ...]
     */
    public static function stkPush(int $orderId, string $phone, float $amount, string $accountRef): array
    {
        $token     = self::getAccessToken();
        $timestamp = date('YmdHis');
        $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

        // M-Pesa requires integer amounts
        $intAmount = (int)ceil($amount);

        $payload = [
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $intAmount,
            'PartyA'            => $phone,
            'PartyB'            => MPESA_SHORTCODE,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => MPESA_CALLBACK_URL,
            'AccountReference'  => substr($accountRef, 0, 12),
            'TransactionDesc'   => 'BabyShopKE Order',
        ];

        $ch = curl_init(MPESA_STK_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => MPESA_ENV === 'production',
        ]);

        $response  = curl_exec($ch);
        $httpCode  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException('STK Push cURL error: ' . $curlError);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['CheckoutRequestID'])) {
            $errMsg = $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'Unknown error';
            throw new RuntimeException('STK Push failed: ' . $errMsg);
        }

        $checkoutRequestId = (string)$data['CheckoutRequestID'];
        $merchantRequestId = (string)$data['MerchantRequestID'];

        // Persist transaction record
        self::createTransaction($orderId, $checkoutRequestId, $merchantRequestId, $phone, $amount);

        // Mark order as awaiting payment
        $db = getDB();
        $db->prepare('UPDATE orders SET status = :s WHERE id = :id')
           ->execute([':s' => 'awaiting_payment', ':id' => $orderId]);

        return [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
        ];
    }

    // ── Status Query ─────────────────────────────────────────────────────────

    /**
     * Poll Daraja for the current STK Push status.
     * Returns a normalized status: 'pending' | 'success' | 'failed'
     * Also updates mpesa_transactions and orders tables accordingly.
     */
    public static function queryStatus(string $checkoutRequestId): array
    {
        // First check our own DB — avoids hitting Daraja repeatedly for settled transactions
        $db          = getDB();
        $stmt        = $db->prepare('SELECT * FROM mpesa_transactions WHERE checkout_request_id = :crid LIMIT 1');
        $stmt->execute([':crid' => $checkoutRequestId]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            return ['status' => 'failed', 'message' => 'Transaction not found.'];
        }

        // Already settled — return immediately
        if (in_array($transaction['status'], ['success', 'failed'], true)) {
            return [
                'status'         => $transaction['status'],
                'message'        => (string)($transaction['result_desc'] ?? ''),
                'mpesa_receipt'  => $transaction['mpesa_receipt'] ?? null,
            ];
        }

        // Still pending — query Daraja
        try {
            $token     = self::getAccessToken();
            $timestamp = date('YmdHis');
            $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

            $payload = [
                'BusinessShortCode' => MPESA_SHORTCODE,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ];

            $ch = curl_init(MPESA_QUERY_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => MPESA_ENV === 'production',
            ]);

            $response  = curl_exec($ch);
            $httpCode  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError || $httpCode !== 200) {
                // Cannot reach Daraja — still pending from our perspective
                return ['status' => 'pending', 'message' => 'Waiting for payment confirmation.'];
            }

            $data       = json_decode($response, true);
            $resultCode = isset($data['ResultCode']) ? (int)$data['ResultCode'] : null;
            $resultDesc = (string)($data['ResultDesc'] ?? '');

            if ($resultCode === 0) {
                // Payment confirmed
                self::markSuccess($checkoutRequestId, null, $resultCode, $resultDesc, $data);
                return ['status' => 'success', 'message' => $resultDesc, 'mpesa_receipt' => null];
            }

            if ($resultCode !== null) {
                // Definitive failure
                self::markFailed($checkoutRequestId, $resultCode, $resultDesc, $data);
                return ['status' => 'failed', 'message' => $resultDesc];
            }

            // STK still in progress (result code not yet available)
            return ['status' => 'pending', 'message' => 'Waiting for payment confirmation.'];
        } catch (Throwable $e) {
            return ['status' => 'pending', 'message' => 'Checking payment status…'];
        }
    }

    // ── Callback handler ─────────────────────────────────────────────────────

    /**
     * Process the POST body sent by Safaricom to the callback URL.
     * Called directly by mpesa_callback.php.
     */
    public static function handleCallback(string $rawBody): void
    {
        $data = json_decode($rawBody, true);

        $stkCallback = $data['Body']['stkCallback'] ?? null;
        if (!$stkCallback) {
            return;
        }

        $checkoutRequestId = (string)($stkCallback['CheckoutRequestID'] ?? '');
        $merchantRequestId = (string)($stkCallback['MerchantRequestID'] ?? '');
        $resultCode        = (int)($stkCallback['ResultCode'] ?? -1);
        $resultDesc        = (string)($stkCallback['ResultDesc'] ?? '');

        if (!$checkoutRequestId) {
            return;
        }

        if ($resultCode === 0) {
            // Extract M-Pesa receipt from CallbackMetadata
            $receipt = null;
            $items   = $stkCallback['CallbackMetadata']['Item'] ?? [];
            foreach ($items as $item) {
                if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                    $receipt = (string)($item['Value'] ?? '');
                    break;
                }
            }
            self::markSuccess($checkoutRequestId, $receipt, $resultCode, $resultDesc, $data);
        } else {
            self::markFailed($checkoutRequestId, $resultCode, $resultDesc, $data);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private static function createTransaction(
        int    $orderId,
        string $checkoutRequestId,
        string $merchantRequestId,
        string $phone,
        float  $amount
    ): void {
        $db = getDB();
        $db->prepare('
            INSERT INTO mpesa_transactions
                (order_id, checkout_request_id, merchant_request_id, phone, amount, status)
            VALUES
                (:order_id, :crid, :mrid, :phone, :amount, "pending")
        ')->execute([
            ':order_id' => $orderId,
            ':crid'     => $checkoutRequestId,
            ':mrid'     => $merchantRequestId,
            ':phone'    => $phone,
            ':amount'   => $amount,
        ]);
    }

    private static function markSuccess(
        string $checkoutRequestId,
        ?string $receipt,
        int    $resultCode,
        string $resultDesc,
        array  $raw
    ): void {
        $db = getDB();

        $db->prepare('
            UPDATE mpesa_transactions
            SET status          = "success",
                mpesa_receipt   = :receipt,
                result_code     = :rc,
                result_desc     = :rd,
                callback_raw    = :raw
            WHERE checkout_request_id = :crid
        ')->execute([
            ':receipt' => $receipt,
            ':rc'      => $resultCode,
            ':rd'      => $resultDesc,
            ':raw'     => json_encode($raw),
            ':crid'    => $checkoutRequestId,
        ]);

        // Fetch linked order and mark it paid
        $stmt = $db->prepare('SELECT order_id FROM mpesa_transactions WHERE checkout_request_id = :crid LIMIT 1');
        $stmt->execute([':crid' => $checkoutRequestId]);
        $row = $stmt->fetch();

        if ($row) {
            $db->prepare('UPDATE orders SET status = "paid" WHERE id = :id')
               ->execute([':id' => $row['order_id']]);
        }
    }

    private static function markFailed(
        string $checkoutRequestId,
        int    $resultCode,
        string $resultDesc,
        array  $raw
    ): void {
        $db = getDB();

        $db->prepare('
            UPDATE mpesa_transactions
            SET status       = "failed",
                result_code  = :rc,
                result_desc  = :rd,
                callback_raw = :raw
            WHERE checkout_request_id = :crid
        ')->execute([
            ':rc'   => $resultCode,
            ':rd'   => $resultDesc,
            ':raw'  => json_encode($raw),
            ':crid' => $checkoutRequestId,
        ]);

        // Revert order back to pending so customer can retry
        $stmt = $db->prepare('SELECT order_id FROM mpesa_transactions WHERE checkout_request_id = :crid LIMIT 1');
        $stmt->execute([':crid' => $checkoutRequestId]);
        $row = $stmt->fetch();

        if ($row) {
            $db->prepare('UPDATE orders SET status = "pending" WHERE id = :id')
               ->execute([':id' => $row['order_id']]);
        }
    }

    // ── Utility ──────────────────────────────────────────────────────────────

    /**
     * Normalise a Kenyan phone number to the 2547XXXXXXXX format required by Daraja.
     * Accepts: 0712345678 | +254712345678 | 254712345678 | 0112345678
     */
    public static function normalisePhone(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '254' . substr($digits, 1);
        }

        if (strlen($digits) === 12 && substr($digits, 0, 3) === '254') {
            return $digits;
        }

        throw new InvalidArgumentException('Invalid Kenyan phone number: ' . $raw);
    }
}

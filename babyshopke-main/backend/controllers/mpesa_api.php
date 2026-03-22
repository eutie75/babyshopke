<?php
declare(strict_types=1);

/**
 * mpesa_api.php
 * -------------
 * JSON REST API consumed by the React frontend.
 *
 * POST /babyshopke/babyshopke-main/backend/controllers/mpesa_api.php
 *   action=initiate  → create order + send STK Push
 *   action=status    → poll payment status by checkout_request_id
 *
 * All responses are JSON.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/mpesa.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Family.php';
require_once __DIR__ . '/../controllers/mpesa_controller.php';

// ── CORS ─────────────────────────────────────────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1:5173',
    'http://localhost:8080',
    'http://127.0.0.1:8080',
    'http://localhost',
    'http://127.0.0.1',
];
if (in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = trim((string)($body['action'] ?? ''));

// ── action: status ───────────────────────────────────────────────────────────
if ($action === 'status') {
    $checkoutRequestId = trim((string)($body['checkout_request_id'] ?? ''));
    if (!$checkoutRequestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'checkout_request_id is required.']);
        exit;
    }

    $result = MpesaController::queryStatus($checkoutRequestId);
    echo json_encode(['success' => true, ...$result]);
    exit;
}

// ── action: initiate ─────────────────────────────────────────────────────────
if ($action === 'initiate') {
    $fullName      = trim((string)($body['full_name'] ?? ''));
    $phone         = trim((string)($body['phone'] ?? ''));
    $address       = trim((string)($body['address'] ?? ''));
    $deliveryOpt   = trim((string)($body['delivery_option'] ?? 'delivery'));
    $mpesaPhone    = trim((string)($body['mpesa_phone'] ?? $phone));
    $cartItems     = $body['cart_items'] ?? [];

    if (!$fullName || !$phone || !$mpesaPhone || empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'full_name, phone, mpesa_phone and cart_items are required.']);
        exit;
    }

    if (!in_array($deliveryOpt, ['delivery', 'pickup'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid delivery_option.']);
        exit;
    }

    try {
        $normalisedPhone = MpesaController::normalisePhone($mpesaPhone);
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

    $subtotal = 0.0;
    foreach ($cartItems as $item) {
        $subtotal += (float)($item['price'] ?? 0) * max(1, (int)($item['qty'] ?? 1));
    }

    $deliveryFee = ($deliveryOpt === 'delivery') ? 250.0 : 0.0;
    $total       = $subtotal + $deliveryFee;

    if ($total <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cart total must be greater than 0.']);
        exit;
    }

    $db = getDB();

    try {
        $db->beginTransaction();

        $orderStmt = $db->prepare('
            INSERT INTO orders
                (user_id, family_id, child_id, total_amount, payment_method, delivery_option, status, full_name, phone, address)
            VALUES
                (:user_id, NULL, NULL, :total, "MPESA", :delivery, "pending", :name, :phone, :address)
        ');
        $userId = isLoggedIn() ? (int)currentUserId() : null;
        $orderStmt->execute([
            ':user_id'  => $userId,
            ':total'    => $total,
            ':delivery' => $deliveryOpt,
            ':name'     => $fullName,
            ':phone'    => $phone,
            ':address'  => ($deliveryOpt === 'delivery') ? $address : 'Pickup',
        ]);
        $orderId = (int)$db->lastInsertId();

        $itemStmt = $db->prepare('
            INSERT INTO order_items (order_id, product_id, price, qty)
            VALUES (:oid, :pid, :price, :qty)
        ');
        foreach ($cartItems as $item) {
            $itemStmt->execute([
                ':oid'   => $orderId,
                ':pid'   => (int)($item['id'] ?? 0),
                ':price' => (float)($item['price'] ?? 0),
                ':qty'   => max(1, (int)($item['qty'] ?? 1)),
            ]);
        }

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()]);
        exit;
    }

    try {
        $stkResult = MpesaController::stkPush(
            $orderId,
            $normalisedPhone,
            $total,
            'Order #' . $orderId
        );
    } catch (Throwable $e) {
        $db->prepare('DELETE FROM orders WHERE id = :id')->execute([':id' => $orderId]);
        http_response_code(502);
        echo json_encode(['success' => false, 'message' => 'STK Push failed: ' . $e->getMessage()]);
        exit;
    }

    echo json_encode([
        'success'              => true,
        'order_id'             => $orderId,
        'checkout_request_id'  => $stkResult['checkout_request_id'],
        'merchant_request_id'  => $stkResult['merchant_request_id'],
        'message'              => 'STK Push sent to ' . $mpesaPhone . '. Enter your PIN to confirm.',
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);

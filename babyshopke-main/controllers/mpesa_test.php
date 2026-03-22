<?php
/**
 * mpesa_test.php
 * --------------
 * Quick diagnostic endpoint. Open in browser or call from frontend to verify:
 *   1. Backend is reachable
 *   2. M-Pesa credentials work (can get access token)
 *   3. ngrok callback URL is set correctly
 *
 * URL: http://localhost/babyshopke/babyshopke-main/backend/controllers/mpesa_test.php
 *
 * DELETE this file before going to production!
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mpesa.php';
require_once __DIR__ . '/../controllers/mpesa_controller.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$result = [
    'backend_reachable' => true,
    'mpesa_env'         => MPESA_ENV,
    'shortcode'         => MPESA_SHORTCODE,
    'callback_url'      => MPESA_CALLBACK_URL,
    'token_test'        => null,
    'token_error'       => null,
    'db_test'           => null,
    'db_error'          => null,
];

// Test M-Pesa token
try {
    $token = MpesaController::getAccessToken();
    $result['token_test'] = 'OK — token received (' . strlen($token) . ' chars)';
} catch (Throwable $e) {
    $result['token_error'] = $e->getMessage();
}

// Test DB connection
try {
    $db = getDB();
    $db->query('SELECT 1');
    $result['db_test'] = 'OK — database connected';
} catch (Throwable $e) {
    $result['db_error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);

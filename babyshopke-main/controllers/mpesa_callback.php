<?php
declare(strict_types=1);

/**
 * mpesa_callback.php
 * ------------------
 * Safaricom posts the STK Push result to this URL.
 * This file must be publicly reachable (use ngrok locally).
 *
 * Endpoint: POST /babyshopke/backend/controllers/mpesa_callback.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mpesa.php';
require_once __DIR__ . '/../controllers/mpesa_controller.php';

// Safaricom only POSTs JSON; reject anything else
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$rawBody = file_get_contents('php://input');
if (!$rawBody) {
    http_response_code(400);
    exit;
}

try {
    MpesaController::handleCallback($rawBody);
} catch (Throwable $e) {
    // Log silently — never return an error to Safaricom (they retry on non-200)
    error_log('[MpesaCallback] ' . $e->getMessage());
}

// Safaricom expects a 200 with this exact body
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);

<?php
declare(strict_types=1);

/**
 * Safaricom Daraja M-Pesa configuration.
 *
 * SANDBOX (development/testing):
 *   MPESA_ENV            = 'sandbox'
 *   MPESA_CONSUMER_KEY   = your sandbox consumer key from developer.safaricom.co.ke
 *   MPESA_CONSUMER_SECRET= your sandbox consumer secret
 *   MPESA_SHORTCODE      = 174379          (sandbox test shortcode)
 *   MPESA_PASSKEY        = bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919 (sandbox)
 *   MPESA_CALLBACK_URL   = publicly reachable URL (use ngrok during dev)
 *
 * PRODUCTION:
 *   MPESA_ENV            = 'production'
 *   MPESA_SHORTCODE      = your actual Paybill/Till number
 *   MPESA_PASSKEY        = passkey from Safaricom portal
 *   MPESA_CALLBACK_URL   = https://yourdomain.com/backend/controllers/mpesa_callback.php
 *
 * HOW TO GET SANDBOX CREDENTIALS:
 *   1. Go to https://developer.safaricom.co.ke/
 *   2. Create account → Create App → tick "Lipa Na M-Pesa Sandbox"
 *   3. Copy Consumer Key & Consumer Secret from your app
 *   4. Use shortcode 174379 and the passkey above for sandbox
 *
 * NGROK FOR LOCAL TESTING (callback URL):
 *   1. Install ngrok: https://ngrok.com/download
 *   2. Run: ngrok http 80
 *   3. Copy the https URL, e.g. https://abc123.ngrok-free.app
 *   4. Set MPESA_CALLBACK_URL = https://abc123.ngrok-free.app/babyshopke/backend/controllers/mpesa_callback.php
 */

define('MPESA_ENV',             'sandbox');   // 'sandbox' | 'production'
define('MPESA_CONSUMER_KEY',    'FDGHlJJy2fs9PEMWogaUpL1IA3MGSWOzyEquesK0Q065S8GR');
define('MPESA_CONSUMER_SECRET', 'UttGWWcvP7zC8FAgZcWijlbAN1qU8YFBX13ozjpNKnPjdcB6Jh0wkbqsTbRSmy3h');
define('MPESA_SHORTCODE',       '174379');    // sandbox shortcode
define('MPESA_PASSKEY',         'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
// ⚠️ UPDATE THIS every time ngrok restarts (free plan URL changes on restart)
// ONLY this callback URL needs ngrok. Your React frontend calls XAMPP directly at localhost.
define('MPESA_CALLBACK_URL',
    'https://diversional-factiously-roosevelt.ngrok-free.dev' .
    '/babyshopke/babyshopke-main/backend/controllers/mpesa_callback.php'
);

// ── Derived constants (do not edit) ─────────────────────────────────────────
define('MPESA_BASE_URL', MPESA_ENV === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke');

define('MPESA_AUTH_URL',    MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials');
define('MPESA_STK_URL',     MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest');
define('MPESA_QUERY_URL',   MPESA_BASE_URL . '/mpesa/stkpushquery/v1/query');

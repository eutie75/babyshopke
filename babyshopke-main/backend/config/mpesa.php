<?php
declare(strict_types=1);

/**
 * ╔══════════════════════════════════════════════════════════════╗
 * ║           BabyShopKE — M-Pesa Daraja Configuration          ║
 * ╚══════════════════════════════════════════════════════════════╝
 *
 * ⚠️  EVERY TIME ngrok restarts, update MPESA_CALLBACK_URL below.
 *     The free plan gives a new URL on every restart.
 *
 *     Steps:
 *       1. Run: ngrok http 80
 *       2. Copy the https://xxxx.ngrok-free.app URL
 *       3. Replace the URL in MPESA_CALLBACK_URL below
 *       4. Save this file — done!
 */

// ── Environment ──────────────────────────────────────────────────────────────
define('MPESA_ENV', 'sandbox'); // Change to 'production' when going live

// ── Sandbox Credentials (from developer.safaricom.co.ke) ────────────────────
define('MPESA_CONSUMER_KEY',    'FDGHlJJy2fs9PEMWogaUpL1IA3MGSWOzyEquesK0Q065S8GR');
define('MPESA_CONSUMER_SECRET', 'UttGWWcvP7zC8FAgZcWijlbAN1qU8YFBX13ozjpNKnPjdcB6Jh0wkbqsTbRSmy3h');
define('MPESA_SHORTCODE',       '174379');
define('MPESA_PASSKEY',         'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');

// ── ⚠️  UPDATE THIS when ngrok restarts ─────────────────────────────────────
define('MPESA_CALLBACK_URL',
    'https://diversional-factiously-roosevelt.ngrok-free.dev' .
    '/babyshopke/babyshopke-main/backend/controllers/mpesa_callback.php'
);

// ── Derived URLs (do not edit) ───────────────────────────────────────────────
define('MPESA_BASE_URL', MPESA_ENV === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke');

define('MPESA_AUTH_URL',  MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials');
define('MPESA_STK_URL',   MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest');
define('MPESA_QUERY_URL', MPESA_BASE_URL . '/mpesa/stkpushquery/v1/query');

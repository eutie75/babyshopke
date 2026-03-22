# 💳 BabyShopKE — Payment Setup Guide

## Quick Start (Every time you work on this project)

### Step 1 — Start XAMPP
Open XAMPP Control Panel → Start **Apache** and **MySQL**

### Step 2 — Start ngrok
Open a terminal and run:
```
ngrok http 80
```
Copy the URL shown, e.g: `https://abc123.ngrok-free.app`

### Step 3 — Update callback URL
Open `backend/config/mpesa.php` and update this line:
```php
define('MPESA_CALLBACK_URL',
    'https://YOUR-NEW-NGROK-URL-HERE' .
    '/babyshopke/babyshopke-main/backend/controllers/mpesa_callback.php'
);
```

### Step 4 — Start React frontend
```
npm run dev
```

### Step 5 — Test the backend is working
Open in browser:
```
http://localhost/babyshopke/babyshopke-main/backend/controllers/mpesa_test.php
```
You should see:
```json
{
  "backend_reachable": true,
  "token_test": "OK — token received (XX chars)",
  "db_test": "OK — database connected"
}
```

---

## File Structure

```
babyshopke-main/
├── backend/
│   ├── config/
│   │   ├── mpesa.php       ← ⚠️ Update MPESA_CALLBACK_URL here when ngrok restarts
│   │   └── db.php          ← DB credentials (root / blank password for XAMPP)
│   └── controllers/
│       ├── mpesa_api.php   ← Frontend calls this for payments
│       ├── mpesa_callback.php ← Safaricom posts payment results here (needs ngrok)
│       └── mpesa_test.php  ← Diagnostic tool (delete before going live!)
└── src/
    └── services/
        └── mpesa.ts        ← React calls this service for all payments
```

## Frontend API Usage

In your React checkout component, import and use like this:

```tsx
import { initiatePayment, pollPaymentUntilSettled } from '@/services/mpesa';

// On checkout submit:
const result = await initiatePayment({
  full_name: 'John Doe',
  phone: '0712345678',
  mpesa_phone: '0712345678',
  address: '123 Nairobi',
  delivery_option: 'delivery',
  cart_items: [{ id: 1, name: 'Baby Toy', price: 500, qty: 2 }]
});

if (result.success) {
  // Poll for payment confirmation
  await pollPaymentUntilSettled(
    result.checkout_request_id,
    (status) => console.log('Payment status:', status.status)
  );
}
```

## Common Errors

| Error | Fix |
|-------|-----|
| "Network error" | XAMPP Apache is not running |
| "STK Push failed" | ngrok is not running or URL not updated in mpesa.php |
| "Database connection failed" | XAMPP MySQL is not running |
| Phone prompt doesn't appear | Use sandbox test number: 254708374149 |

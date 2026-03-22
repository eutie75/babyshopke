# Baby Shop KE Backend (PHP + MySQL)

## Stack
- PHP 8+
- MySQL / MariaDB
- PDO prepared statements
- Sessions + CSRF
- Vanilla JS + custom CSS

## Folder Structure
```
backend/
  assets/
    app.js
    styles.css
  config/
    config.php
    csrf.php
    db.php
  controllers/
    auth_controller.php
    cart_controller.php
    family_controller.php
    order_controller.php
    product_controller.php
    wishlist_controller.php
  includes/
    admin_guard.php
    auth_guard.php
    flash.php
    footer.php
    header.php
    navbar.php
  models/
    Cart.php
    Family.php
    Order.php
    Product.php
    User.php
    Wishlist.php
  public/
    index.php
    getstarted.php
    login.php
    register.php
    account.php
    wishlist.php
    cart.php
    checkout.php
    order_confirmation.php
    orders.php
    order_view.php
    product.php
    logout.php
    admin/
      dashboard.php
      products.php
      product_add.php
      product_edit.php
      product_delete.php
      orders.php
      order_update.php
  database.sql
```

## XAMPP Setup
1. Copy the contents of `backend/` to:
   `C:\xampp\htdocs\babyshopke\`

2. Start Apache + MySQL from XAMPP.

3. Open phpMyAdmin and import:
   `database.sql`

4. Confirm DB credentials in `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'babyshopke');
define('DB_USER', 'root');
define('DB_PASS', '');
```

5. Confirm app URL in `config/config.php`:
```php
define('SITE_URL', 'http://localhost/babyshopke/public');
```

6. Open:
   `http://localhost/babyshopke/public/index.php`

## Seed Accounts
- Admin:
  - Email: `admin@babyshopke.co.ke`
  - Password: `Admin@123`
- User:
  - Email: `jane@example.com`
  - Password: `User@123`

If seeded password does not work in your PHP build, register a new account from `register.php`.

## Key Functional Flows
- Get Started page with working CTA links.
- Register/login/logout with password hashing and verification.
- Account page with:
  - Profile update
  - Family creation
  - Child profile creation
  - Active child selection
- Homepage with:
  - Age filter tabs (`?age=0-3,3-6,6-12,12-18,24-48`)
  - Category filter (`?cat=...`)
  - Search filter (`?q=...`)
  - Age-based recommendations from active child
- Wishlist:
  - Add/remove/toggle
  - Move wishlist item to cart
- Cart:
  - Add/increase/decrease/remove/clear
  - Stock validation enforced
  - Cart badge in navbar
- Checkout:
  - Transactional order + order_items creation
  - Atomic stock deduction
  - Cart clear after checkout
- Orders:
  - User order history and detail view
- Admin:
  - Dashboard cards (orders/products/low stock)
  - Product CRUD (add/edit/delete)
  - Order status updates (pending/paid/shipped/delivered)


# 🍼 BabyShopKe
Premium Baby & Kids E-Commerce Platform  
PHP + MySQL + Modern Frontend

## 📌 Project Overview
BabyShopKe is a premium web-based e-commerce platform for baby and kids products.

Core capabilities:
- 🛒 Dynamic product catalog
- 👨‍👩‍👧 Family account system
- 🎯 Age-based product recommendations
- 🧾 Secure checkout flow
- 📦 Inventory updates
- 🔐 Role-based access control (User/Admin)

## 🎨 Brand Identity
Primary colors:
- Turquoise: `#2EC4B6`
- Pastel Crimson: `#FF6B8A`
- Light Background: `#FFF7F2`
- Dark Text: `#1F2933`

Design style:
- Premium, soft, modern baby boutique aesthetic
- Rounded UI components
- Subtle shadows and glassmorphism touches

## 🛠 Technology Stack
Frontend:
- HTML5
- CSS3
- JavaScript
- React + Vite (current UI)

Backend:
- PHP (server-side logic)
- MySQL (database)
- XAMPP (local development)

Tools:
- VS Code / Cursor / Codex
- GitHub
- Draw.io (ERD)
- Figma (UI)

## ⚙ System Features
### 👤 Authentication
- User registration
- User login/logout
- Password hashing (`password_hash`)
- Session management
- Role-based access (Admin/User)

### 👨‍👩‍👧 Family Accounts
- Create family profile
- Add child profiles (name + DOB)
- Set active child
- Active child used for recommendation filtering

### 🛍 Product Management (Admin)
- Dashboard
- Add/edit/delete products
- Manage stock
- View and update orders

Product fields:
- Name
- Description
- Price
- Image
- Category
- Stock
- `age_min_months`
- `age_max_months`

### 🎯 Age-Based Recommendation Engine
How it works:
1. System calculates child age in months.
2. Products are filtered with:
   `age_min_months <= child_age <= age_max_months`
3. Shows “Top Picks for X Months”.
4. If no child is selected, default age range is `6–12 months`.

### 🛒 Cart
- Add to cart
- Update quantity
- Remove item
- Stock validation
- Cart badge counter

### 💳 Checkout
- Customer details form
- Delivery option
- Payment simulation:
  - M-Pesa (simulated)
  - Cash on Delivery
- Order saved to database
- Stock updated automatically
- Order confirmation page

### 📦 Orders
- Users can view order history
- Admin can:
  - View all orders
  - Change status (`Pending`, `Paid`, `Shipped`, `Delivered`)

## 🗂 Project Structure
```text
babyshopke/
├── src/                     # React frontend
├── public/                  # frontend public assets
├── backend/                 # PHP + MySQL app
│   ├── assets/
│   ├── config/
│   ├── controllers/
│   ├── includes/
│   ├── models/
│   ├── public/
│   │   └── admin/
│   └── database.sql
└── README.md
```

## 🗄 Database Structure
Main tables:
- `users`
- `families`
- `family_members`
- `children`
- `products`
- `orders`
- `order_items`

Relationships:
- User → Family
- Family → Children
- Orders → Users
- Orders → Order Items
- Order Items → Products

## 🚀 Installation Guide (XAMPP)
1. Install XAMPP and start Apache + MySQL.
2. Create database `babyshopke` in phpMyAdmin.
3. Import `backend/database.sql`.
4. Update DB credentials in `backend/config/db.php`.
5. Place backend app in:
   `C:\xampp\htdocs\babyshopke\`
6. Open:
   `http://localhost/babyshopke/public/index.php`

Example DB credentials:
```php
$host = "localhost";
$db   = "babyshopke";
$user = "root";
$pass = "";
```

## 🔐 Security Measures
- Password hashing
- Prepared statements (PDO)
- CSRF tokens
- Input validation
- Output escaping (`htmlspecialchars`)
- Role-based route protection

## 📈 Non-Functional Requirements
- Responsive UI
- Scalable DB structure
- Secure data handling
- Real-time stock updates
- Local server availability (XAMPP)

## 🎓 Academic Objectives Achieved
- ✔ Frontend and backend integration
- ✔ CRUD operations
- ✔ Dynamic content loading
- ✔ E-commerce transaction simulation
- ✔ Authentication and authorization
- ✔ Recommendation engine implementation

## 👥 Project Team Roles
- Project Manager
- UI/UX Designer
- Frontend Developer
- Backend Developer
- Database Administrator
- Security Analyst
- QA Engineer

## 📌 Conclusion
BabyShopKe digitizes baby retail operations by:
- Improving product accessibility
- Automating inventory management
- Securing transaction handling
- Enhancing customer experience
- Delivering intelligent age-based recommendations

It demonstrates practical e-commerce architecture using PHP and MySQL.

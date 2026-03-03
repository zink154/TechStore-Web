# TechStore

E-commerce demo web app built with Laravel 12 + Vue 3.

## Tech Stack

**Backend:** PHP 8.3, Laravel 12, Sanctum, Spatie Permission, PostgreSQL, Cloudinary, Stripe, Resend

**Frontend:** Vue 3, Vite, Pinia, Vue Router 4, Tailwind CSS 4, Axios, Stripe.js

## Features

- Product catalog with category filter, search, pagination
- Shopping cart (Pinia, persisted to localStorage)
- Checkout with Stripe (sandbox)
- Order history & status tracking
- Email confirmation via Resend
- Admin panel: products, categories, orders management
- Role-based access (admin / customer)

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@techstore.test | password |
| Customer | user@techstore.test | password |

## Local Setup

### Prerequisites
- PHP 8.3, Composer
- Node.js 20+, npm
- PostgreSQL

### Backend

```bash
cd backend
cp .env.example .env
# Fill in DB credentials, Cloudinary, Stripe, Resend keys
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend

```bash
cd frontend
cp .env.example .env
# Fill in VITE_STRIPE_KEY
npm install
npm run dev
```

Backend runs on `http://localhost:8000`, frontend on `http://localhost:5173`.

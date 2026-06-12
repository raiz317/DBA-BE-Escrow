# 🚀 SETUP GUIDE - BeDevbis Marketplace API v1

## ⚡ Quick Start (5 Menit)

### 1. Install Dependencies
```bash
cd c:\xampp\htdocs\BeDevbis
composer install
npm install
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Setup Database
Buat database baru di MySQL:
```bash
mysql -u root -p
> CREATE DATABASE bedevbis_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> EXIT;
```

Jalankan migration:
```bash
php artisan migrate
```

### 4. Jalankan Server
```bash
php artisan serve
# API akan accessible di http://localhost:8000
```

---

## 📚 Testing API

### Menggunakan cURL

**1. Register**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Seller",
    "email": "seller@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "seller"
  }'
```

**2. Login**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "seller@example.com",
    "password": "password123"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {...},
    "token": "1|Zy1a2b3c4d5e6f7g8h9i0j..."
  }
}
```

**3. Create Product (gunakan token dari response login)**
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "name": "Laptop Gaming Asus",
    "description": "Laptop gaming dengan spesifikasi tinggi",
    "price": 15000000,
    "stock": 10,
    "image_url": "https://example.com/laptop.jpg",
    "status": "active"
  }'
```

### Menggunakan Postman

1. **Import Collection**
   - Buka Postman
   - Klik Import → Paste raw text
   - Copy-paste endpoints dari [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

2. **Setup Environment Variables**
   - Create environment: "BeDevbis Dev"
   - Add variable: `base_url = http://localhost:8000/api`
   - Add variable: `token = ` (diisi setelah login)

3. **Use {{base_url}} dan {{token}} di requests**

---

## 🗂️ Struktur File yang Dibuat

### Controllers (`app/Http/Controllers/API/`)
```
AuthController.php      → register, login, logout, me
ProductController.php   → CRUD produk, list seller products
OrderController.php     → order management, shipping, confirmation
PaymentController.php   → payment processing
```

### Models (`app/Models/`)
```
User.php        → User dengan role (seller/buyer)
Product.php     → Produk dengan seller relationship
Order.php       → Order dengan buyer, seller, product relationship
Payment.php     → Payment record
EscrowLog.php   → Audit trail untuk escrow
```

### Form Requests (`app/Http/Requests/`)
```
RegisterRequest.php      → Validasi register
LoginRequest.php         → Validasi login
CreateProductRequest.php → Validasi create produk
UpdateProductRequest.php → Validasi update produk
CreateOrderRequest.php   → Validasi create order
PayOrderRequest.php      → Validasi payment
ShipOrderRequest.php     → Validasi shipping
```

### API Resources (`app/Http/Resources/`)
```
UserResource.php        → Serialisasi user
ProductResource.php     → Serialisasi produk dengan seller
OrderResource.php       → Serialisasi order dengan relasi
PaymentResource.php     → Serialisasi payment
EscrowLogResource.php   → Serialisasi escrow log
```

### Database Migrations (`database/migrations/`)
```
*_update_users_table.php              → Add role column
*_create_products_table.php           → Products table
*_create_orders_table.php             → Orders table
*_create_payments_table.php           → Payments table
*_create_escrow_logs_table.php        → Escrow logs table
```

### Configuration
```
config/cors.php         → CORS setup
routes/api.php          → All API routes
.env.example            → Environment template
Procfile                → Heroku/Railway deployment
railway.toml            → Railway deployment config
```

---

## 🔐 Authentication Flow

```
1. POST /api/auth/register
   ↓
   Database: Create user dengan role
   ↓
   Response: User + Token (7 hari valid)

2. POST /api/auth/login
   ↓
   Database: Validate email & password
   ↓
   Response: User + Token

3. Use token di header Authorization:
   Authorization: Bearer {token}
   ↓
   All protected endpoints accessible

4. POST /api/auth/logout
   ↓
   Database: Revoke token
   ↓
   Token no longer valid
```

---

## 📊 Database Schema

### Users
```sql
id (PK) | name | email (UNIQUE) | password | role (enum) | email_verified_at | created_at | updated_at
```

### Products
```sql
id (PK) | seller_id (FK) | name | description | price | stock | image_url | status | created_at | updated_at
```

### Orders
```sql
id (PK) | buyer_id (FK) | product_id (FK) | seller_id (FK) | quantity | total_price | status | tracking_number | created_at | updated_at
```

### Payments
```sql
id (PK) | order_id (FK) | amount | method | status | paid_at | created_at | updated_at
```

### Escrow_logs
```sql
id (PK) | order_id (FK) | actor_id (FK) | action | amount | note | created_at | updated_at
```

---

## 🎯 Order Status Workflow

```
                    ┌─────────────────┐
                    │ pending_payment │
                    └────────┬────────┘
                            │
                    ┌─────────▼────────┐
                    │      paid        │ ◄─── Payment via /api/orders/{id}/pay
                    └────────┬────────┘
                            │
                    ┌─────────▼────────┐
                    │   processing     │
                    └────────┬────────┘
                            │
                    ┌─────────▼────────┐
                    │     shipped      │ ◄─── Seller update via /api/seller/orders/{id}/ship
                    └────────┬────────┘
                            │
                    ┌─────────▼────────┐
                    │   delivered      │
                    └────────┬────────┘
                            │
                    ┌─────────▼────────┐
                    │    completed     │ ◄─── Buyer confirm via /api/orders/{id}/confirm
                    └──────────────────┘

Refund/Cancel:
pending_payment ──────► cancelled (buyer action)
any status ────► refunded (admin/support action)
```

---

## 🚢 Deployment ke Railway

### 1. Install Railway CLI
```bash
npm install -g @railway/cli
```

### 2. Link Project
```bash
railway link
# Atau: railway init (untuk project baru)
```

### 3. Add MySQL Plugin
```bash
railway add
# Select: MySQL
```

### 4. Configure Environment
```bash
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set FRONTEND_URL=https://yourdomain.com
```

### 5. Deploy
```bash
railway up
```

### 6. Run Migrations
```bash
railway run php artisan migrate --force
```

### Hasil
- API accessible di: `https://bedevbis-prod.up.railway.app`
- MySQL tersedia via DATABASE_URL (auto-injected)

---

## 🧪 Testing Checklist

### Authentication
- [ ] Register sebagai seller
- [ ] Register sebagai buyer
- [ ] Login berhasil mendapat token
- [ ] Token bisa digunakan di protected endpoints
- [ ] Logout revoke token
- [ ] Rate limiting 5x/menit pada login

### Products (Seller)
- [ ] Create product
- [ ] Update product sendiri
- [ ] Tidak bisa update produk orang lain
- [ ] Delete product
- [ ] List seller products

### Products (Public)
- [ ] List products dengan pagination
- [ ] Search products
- [ ] Filter by price range
- [ ] View product detail

### Orders (Buyer)
- [ ] Create order
- [ ] List own orders
- [ ] View order detail
- [ ] Cancel order (hanya saat pending_payment)

### Orders (Seller)
- [ ] View orders masuk
- [ ] Ship order dengan tracking number
- [ ] Tidak bisa ship order lain

### Payments
- [ ] Pay order mengubah status ke paid
- [ ] Payment record dibuat
- [ ] Escrow log dicatat
- [ ] Coba bayar order yang sudah paid (gagal)

---

## 🐛 Troubleshooting

### Database Connection Error
```
Error: SQLSTATE[HY000] [1045] Access denied
```
**Solution**: Update DB_USERNAME, DB_PASSWORD, DB_DATABASE di .env

### Token Expired Error
```
Error: Unauthenticated
```
**Solution**: User perlu login ulang untuk token baru

### CORS Error di Frontend
```
Access to XMLHttpRequest blocked by CORS policy
```
**Solution**: Update FRONTEND_URL di .env dan restart server

### Migration Error
```
Column already exists
```
**Solution**: Tidak bisa run migration 2x. Gunakan:
```bash
php artisan migrate:refresh
```

---

## 📞 Debug Commands

```bash
# Interactive shell
php artisan tinker
>>> User::all()
>>> Order::with('buyer', 'seller', 'product')->get()

# View routes
php artisan route:list

# Check migrations
php artisan migrate:status

# Clear cache
php artisan cache:clear

# View logs
tail -f storage/logs/laravel.log
```

---

## 📖 Dokumentasi Lengkap

- **API Endpoints**: Lihat [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Project Overview**: Lihat [README.md](README.md)
- **Environment Setup**: Lihat [.env.example](.env.example)

---

## ✅ Fitur yang Sudah Siap

✅ User Authentication dengan Sanctum
✅ Product Management (Seller CRUD)
✅ Order Management dengan Escrow
✅ Payment Simulation
✅ Role-based Access Control
✅ Rate Limiting
✅ CORS Configuration
✅ API Documentation
✅ Database Migrations
✅ Form Validation
✅ API Resources
✅ Authorization Policies
✅ Pagination & Filtering
✅ Railway Deployment Ready

---

**Status**: ✅ SIAP DIGUNAKAN

Semua file telah dibuat dan dikonfigurasi. Silakan ikuti Quick Start di atas untuk memulai!

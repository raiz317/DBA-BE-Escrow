# BeDevbis Marketplace API v1

REST API untuk aplikasi marketplace berbasis escrow otomatis, dibangun dengan Laravel 11 dan MySQL.

## Stack Teknologi

- **Backend**: Laravel 11
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Deployment**: Railway

## Setup Lokal

### Prasyarat

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (untuk frontend development)

### Instalasi

1. **Clone repository dan install dependencies**
   ```bash
   cd BeDevbis
   composer install
   npm install
   ```

2. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi database**
   ```bash
   # Edit .env dan sesuaikan database credentials
   DB_DATABASE=bedevbis_marketplace
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Migration dan Seeding**
   ```bash
   php artisan migrate
   # Opsional: php artisan seed (jika ada seeder)
   ```

5. **Jalankan server**
   ```bash
   php artisan serve
   ```
   API akan accessible di `http://localhost:8000`

## Dokumentasi API

### Base URL
```
http://localhost:8000/api
```

### Authentication
Gunakan Bearer token di header Authorization:
```
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### 1. Register
**POST** `/auth/register`

Request:
```json
{
  "name": "Nama User",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "buyer" // atau "seller"
}
```

Response:
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Nama User",
      "email": "user@example.com",
      "role": "buyer",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "token": "bearer_token_here"
  }
}
```

### 2. Login
**POST** `/auth/login`

Request:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": { /* user data */ },
    "token": "bearer_token_here"
  }
}
```

**Rate Limiting**: Max 5 login attempts per IP per minute

### 3. Logout
**POST** `/auth/logout` (Protected)

Response:
```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null
}
```

### 4. Get Current User
**GET** `/me` (Protected)

Response:
```json
{
  "success": true,
  "message": "Data user berhasil diambil",
  "data": { /* user data */ }
}
```

---

## Product Endpoints

### 1. List Products (Public)
**GET** `/products`

Query Parameters:
- `search` - Cari berdasarkan nama/deskripsi
- `min_price` - Filter harga minimum
- `max_price` - Filter harga maksimum
- `page` - Halaman (default 1)

Example:
```bash
GET /products?search=laptop&min_price=1000000&max_price=20000000&page=1
```

Response:
```json
{
  "success": true,
  "message": "Daftar produk berhasil diambil",
  "data": [
    {
      "id": 1,
      "seller_id": 1,
      "name": "Laptop Gaming",
      "description": "Laptop gaming terbaik",
      "price": 15000000,
      "stock": 10,
      "image_url": "https://example.com/image.jpg",
      "status": "active",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 12,
    "current_page": 1,
    "last_page": 9
  }
}
```

### 2. Get Product Detail (Public)
**GET** `/products/{id}`

Response:
```json
{
  "success": true,
  "message": "Detail produk berhasil diambil",
  "data": { /* product data */ }
}
```

### 3. Create Product (Seller Only, Protected)
**POST** `/products`

Request:
```json
{
  "name": "Nama Produk",
  "description": "Deskripsi produk",
  "price": 500000,
  "stock": 10,
  "image_url": "https://example.com/image.jpg",
  "status": "active"
}
```

### 4. Update Product (Seller Only, Protected)
**PUT** `/products/{id}`

Request:
```json
{
  "name": "Nama Produk Updated",
  "price": 600000,
  "stock": 15
}
```

### 5. Delete Product (Seller Only, Protected)
**DELETE** `/products/{id}`

### 6. List Seller's Products (Seller Only, Protected)
**GET** `/seller/products`

Query Parameters: sama seperti list products

---

## Order Endpoints

### 1. Create Order (Buyer Only, Protected)
**POST** `/orders`

Request:
```json
{
  "product_id": 1,
  "quantity": 2
}
```

Response:
```json
{
  "success": true,
  "message": "Order berhasil dibuat",
  "data": {
    "id": 1,
    "buyer_id": 2,
    "product_id": 1,
    "seller_id": 1,
    "quantity": 2,
    "total_price": 10000000,
    "status": "pending_payment",
    "tracking_number": null,
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

### 2. List Buyer Orders (Buyer Only, Protected)
**GET** `/orders`

Pagination: 12 item per halaman

### 3. Get Order Detail (Protected)
**GET** `/orders/{id}`

Accessible oleh: buyer atau seller yang terkait

### 4. List Seller Orders (Seller Only, Protected)
**GET** `/seller/orders`

### 5. Confirm Order Receipt (Buyer Only, Protected)
**PUT** `/orders/{id}/confirm`

Status harus `delivered` sebelumnya. Mengubah status menjadi `completed` dan merilis dana ke seller.

### 6. Cancel Order (Buyer Only, Protected)
**PUT** `/orders/{id}/cancel`

Hanya bisa dilakukan jika status adalah `pending_payment`

### 7. Ship Order (Seller Only, Protected)
**PUT** `/seller/orders/{id}/ship`

Request:
```json
{
  "tracking_number": "JNE123456789"
}
```

Mengubah status order menjadi `shipped`

---

## Payment Endpoints

### 1. Pay Order (Buyer Only, Protected)
**POST** `/orders/{id}/pay`

Request:
```json
{
  "payment_method": "bank_transfer" // atau "virtual_account", "ewallet"
}
```

Response:
```json
{
  "success": true,
  "message": "Pembayaran berhasil diproses",
  "data": {
    "id": 1,
    "order_id": 1,
    "amount": 10000000,
    "method": "bank_transfer",
    "status": "success",
    "paid_at": "2024-01-01T00:00:00Z",
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

**Note**: Untuk v1, pembayaran langsung di-set sebagai `success`. Integrasi payment gateway bisa dilakukan di v2.

---

## Order Status Flow

```
pending_payment → paid → processing → shipped → delivered → completed
                    ↓
                  refunded (jika ada refund)
                    
pending_payment → cancelled (buyer bisa cancel hanya di status ini)
```

---

## Error Response Format

```json
{
  "success": false,
  "message": "Pesan error",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

---

## CORS Configuration

API dikonfigurasi untuk menerima request dari:
- `http://localhost:3000` (development frontend)
- Domain yang dikonfigurasi di `.env` via `FRONTEND_URL`

---

## Deployment ke Railway

### 1. Setup Railway Project
```bash
railway link
# atau buat project baru di railway.app
```

### 2. Add MySQL Plugin
```bash
railway add
# Pilih MySQL
```

### 3. Configure Environment
Railway akan auto-inject `DATABASE_URL`. Pastikan di `.env`:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
```

### 4. Deploy
```bash
railway up
# atau push ke git yang terhubung dengan Railway
```

### 5. Run Migrations
```bash
railway run php artisan migrate --force
```

---

## Token Expiry

- Token berlaku selama **7 hari**
- Setelah expiry, user harus login kembali

---

## Rate Limiting

- **Login endpoint**: 5 attempts per menit per IP address

---

## Struktur Database

### Users Table
```
- id (PK)
- name
- email (unique)
- password (hashed)
- role (enum: seller, buyer)
- email_verified_at
- timestamps
```

### Products Table
```
- id (PK)
- seller_id (FK users)
- name
- description
- price (decimal)
- stock (integer)
- image_url (nullable)
- status (enum: active, inactive)
- timestamps
```

### Orders Table
```
- id (PK)
- buyer_id (FK users)
- product_id (FK products)
- seller_id (FK users)
- quantity (integer)
- total_price (decimal)
- status (enum: pending_payment, paid, processing, shipped, delivered, completed, cancelled, refunded)
- tracking_number (nullable)
- timestamps
```

### Payments Table
```
- id (PK)
- order_id (FK orders)
- amount (decimal)
- method (enum: bank_transfer, virtual_account, ewallet)
- status (enum: pending, success, failed)
- paid_at (timestamp, nullable)
- timestamps
```

### Escrow Logs Table
```
- id (PK)
- order_id (FK orders)
- actor_id (FK users)
- action (string)
- amount (decimal, nullable)
- note (text, nullable)
- timestamps
```

---

## Development Tips

1. **Testing dengan Postman/Insomnia**
   - Import collection dari documentation
   - Setup environment variables untuk token

2. **Database Inspection**
   ```bash
   php artisan tinker
   >>> User::all()
   >>> Order::with('buyer', 'seller', 'product')->get()
   ```

3. **Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Refresh Database**
   ```bash
   php artisan migrate:refresh
   ```

---

## Roadmap v2

- [ ] Integrasi payment gateway (Midtrans, Xendit)
- [ ] Email notifications
- [ ] Seller ratings & reviews
- [ ] Search & filtering advanced
- [ ] Admin dashboard
- [ ] Webhook untuk payment confirmation
- [ ] Two-factor authentication
- [ ] Dispute resolution system

---

## License

Proprietary - Hak Cipta © 2024 BeDevbis

---

## Support

Untuk pertanyaan atau issue, hubungi tim development.

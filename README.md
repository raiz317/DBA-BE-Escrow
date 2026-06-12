# BeDevbis Marketplace API v1

Backend REST API untuk aplikasi marketplace dengan sistem escrow otomatis, dibangun menggunakan **Laravel 11** dan **MySQL**.

## 🚀 Fitur Utama

- **Autentikasi** - Register & Login dengan Laravel Sanctum
- **Manajemen Produk** - CRUD produk untuk seller
- **Sistem Order** - Buyer dapat membeli produk dengan workflow yang jelas
- **Escrow Otomatis** - Dana ditahan hingga buyer mengkonfirmasi penerimaan
- **Pembayaran Simulasi** - Implementasi payment untuk v1, gateway payment bisa diintegrasikan di v2
- **Role-based Access** - Akses berbeda untuk Seller dan Buyer

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ (optional, untuk frontend)

## 🛠️ Quick Start

### 1. Setup Environment
```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup
```bash
# Create database
mysql -u root -p
> CREATE DATABASE bedevbis_marketplace;

# Run migrations
php artisan migrate

# (Optional) Seed data
php artisan seed
```

### 3. Run Server
```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## 📖 API Documentation

Dokumentasi lengkap API tersedia di [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

### Quick API Overview

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/auth/register` | Register user | No |
| POST | `/api/auth/login` | Login user | No |
| POST | `/api/auth/logout` | Logout user | Yes |
| GET | `/api/me` | Get current user | Yes |
| GET | `/api/products` | List all products | No |
| GET | `/api/products/{id}` | Get product detail | No |
| POST | `/api/products` | Create product | Yes (Seller) |
| PUT | `/api/products/{id}` | Update product | Yes (Seller) |
| DELETE | `/api/products/{id}` | Delete product | Yes (Seller) |
| GET | `/api/seller/products` | List seller's products | Yes |
| POST | `/api/orders` | Create order | Yes (Buyer) |
| GET | `/api/orders` | List buyer orders | Yes |
| GET | `/api/orders/{id}` | Get order detail | Yes |
| POST | `/api/orders/{id}/pay` | Pay order | Yes (Buyer) |
| PUT | `/api/orders/{id}/confirm` | Confirm delivery | Yes (Buyer) |
| PUT | `/api/orders/{id}/cancel` | Cancel order | Yes (Buyer) |
| GET | `/api/seller/orders` | List seller orders | Yes (Seller) |
| PUT | `/api/seller/orders/{id}/ship` | Ship order | Yes (Seller) |

## 🗂️ Project Structure

```
app/
├── Http/
│   ├── Controllers/API/        # API Controllers
│   ├── Requests/               # Form Requests (Validation)
│   └── Resources/              # API Resources (Transformers)
├── Models/                      # Eloquent Models
├── Policies/                    # Authorization Policies
database/
├── migrations/                  # Database Migrations
└── seeders/                     # Database Seeders
routes/
├── api.php                      # API Routes
└── web.php                      # Web Routes
```

## 🔐 Authentication

Menggunakan **Laravel Sanctum** untuk API token-based authentication.

- Generate token saat login
- Token berlaku **7 hari**
- Include token di header: `Authorization: Bearer {token}`

## 💾 Database Schema

**Users** - id, name, email, password, role (seller/buyer), timestamps

**Products** - id, seller_id, name, description, price, stock, image_url, status, timestamps

**Orders** - id, buyer_id, product_id, seller_id, quantity, total_price, status, tracking_number, timestamps

**Payments** - id, order_id, amount, method, status, paid_at, timestamps

**Escrow Logs** - id, order_id, actor_id, action, amount, note, timestamps

## 🚀 Deployment ke Railway

Panduan detail deployment ada di [RAILWAY_DEPLOY.md](RAILWAY_DEPLOY.md).

### 1. Link Railway Project
```bash
railway link
```

### 2. Add MySQL
```bash
railway add
```

### 3. Configure dan Deploy
```bash
# Set production env
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false

# Deploy
railway up
```

### 4. Run Migrations
```bash
railway run php artisan migrate --force
```

Lihat [.env.example](.env.example) untuk konfigurasi lengkap.

## 📝 Response Format

Semua response dalam format JSON dengan struktur konsisten:

```json
{
  "success": true/false,
  "message": "Pesan response",
  "data": {},
  "errors": {}
}
```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 📚 Additional Commands

```bash
# Refresh database (truncate + migrate)
php artisan migrate:refresh

# View routes
php artisan route:list

# Interactive shell
php artisan tinker

# Clear cache
php artisan cache:clear
```

## 🐛 Troubleshooting

**Permission Denied**
```bash
chmod -R 775 storage bootstrap/cache
```

**Database Connection Error**
Pastikan MySQL running dan credentials di .env benar.

**Token Expired**
User perlu login kembali untuk mendapatkan token baru.

## 📦 Dependencies Utama

- `laravel/framework` - Web framework
- `laravel/sanctum` - API authentication
- `laravel/eloquent` - ORM

## 🔜 Roadmap v2

- [ ] Integrasi payment gateway (Midtrans/Xendit)
- [ ] Email notifications
- [ ] Seller ratings & reviews
- [ ] Admin dashboard
- [ ] Webhook support
- [ ] Two-factor authentication
- [ ] Dispute resolution system

## 📄 License

Proprietary - Hak Cipta © 2024 BeDevbis

## 👥 Contributors

Tim Development BeDevbis

---

**Perlu bantuan?** Hubungi tim development atau buka issue di repository ini.

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


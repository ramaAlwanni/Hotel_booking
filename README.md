# 🏨 Hotel Booking System

A hotel room booking system built with **Laravel** to simplify room reservations and hotel management.

---

## 🚀 Requirements

Before running the project, make sure you have the following installed:

- PHP >= 8.2
- Composer
- MySQL
- XAMPP or any local server environment

---

## ⚙️ Installation & Setup

### 1. Clone the repository
```bash
git clone https://github.com/ramaAlwanni/Hotel_booking.git
```
### 2. Install dependencies
```bash
composer install
```
### 3. Environment configuration
```bash
cp .env.example .env
```
### 4. Generate application key
```bash
php artisan key:generate
```
### 5. Configure database settings in .env
env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=root
DB_PASSWORD=
### 6. Run database migrations
```bash
php artisan migrate
```
### 7. Start the development server
```bash
php artisan serve
```
### 8. Open your browser and visit:
http://127.0.0.1:8000

### 📁 Project Structure
app/Models/ – Eloquent models (User, Hotel, Booking, ...)
app/Http/Controllers/ – Application controllers
routes/web.php – Web routes
database/migrations/ – Database schema migrations
resources/views/ – Blade view templates

### 🛠️ Tech Stack
Laravel 12 – PHP Framework
MySQL – Relational Database
Spatie/laravel-permission – Role & Permission Management

### 🧪 Upcoming Features
User authentication & role-based access control
Hotel & room management (CRUD)
Booking system with payment integration (Stripe)
Admin dashboard built with React
Email notifications & queue system
Docker & CI/CD support

### 👤 Author
Rama Alwanni
GitHub: [@ramaAlwanni](https://github.com/rama)


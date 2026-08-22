# 🏨 Hotel Booking System

نظام حجز فنادق مبني باستخدام **Laravel**، يهدف إلى تسهيل عملية حجز الغرف وإدارة الفنادق.

---

## 🚀 المتطلبات الأساسية

قبل تشغيل المشروع، تأكد من تثبيت ما يلي:

- PHP >= 8.2
- Composer
- MySQL
- XAMPP
---

## ⚙️ خطوات التثبيت والتشغيل

1. **استنساخ المشروع**
```bash
git clone https://github.com/ramaAlwanni/Hotel_booking.git
cd Hotel_booking
```

2. **تثبيت الحزم**
```bash
composer install
```

3. **إعداد ملف البيئة**
```bash
cp .env.example .env
```

4. **توليد مفتاح التطبيق**
```bash
php artisan key:generate
```

5. **تعديل إعدادات قاعدة البيانات** في ملف `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=root
DB_PASSWORD=
```

6. **تشغيل الهجرات (المigrations)**
```bash
php artisan migrate
```

7. **تشغيل الخادم المحلي**
```bash
php artisan serve
```

8. **فتح المتصفح على الرابط:**  
   [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🗂️ هيكل المشروع

- `app/Models/` - نماذج البيانات (مثل `User`, `Hotel`, `Booking`)
- `app/Http/Controllers/` - المتحكمات
- `routes/web.php` - مسارات الويب
- `database/migrations/` - هجرات قاعدة البيانات
- `resources/views/` - ملفات الواجهة (Blade)

---

## 🛠️ الأدوات المستخدمة

- **Laravel 12** - إطار العمل
- **MySQL** - قاعدة البيانات
- **Spatie/laravel-permission** - إدارة الصلاحيات

---

## 👤 المطور

**Rama Alwanni**  
GitHub: [ramaAlwanni](https://github.com/ramaAlwanni)

---

## 📄 الترخيص

هذا المشروع مفتوح المصدر بموجب ترخيص [MIT](https://opensource.org/licenses/MIT).
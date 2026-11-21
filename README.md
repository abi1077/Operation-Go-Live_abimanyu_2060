# Garuda-Hub (Tugas Refactoring MVC Laravel)

Tugas refactoring sistem lelang proyek IT **Garuda-Hub** dari PHP Native (*spaghetti code*) ke framework **Laravel** dengan arsitektur **MVC** dan **Eloquent ORM**.

## Struktur & Perubahan Sistem

1. **Database & Migrations**
   - Skema database diatur ulang lewat file migrasi di `database/migrations/`:
     - `users`: data akun (klien, developer, admin).
     - `projects`: data proyek yang dibuat oleh klien.
     - `bids`: penawaran proyek dari developer.
   - Relasi antar tabel:
     - User (klien) -> Projects (One to Many)
     - Project -> Bids (One to Many)
     - User (developer) -> Bids (One to Many)

2. **Model & Eloquent ORM**
   - File model berada di `app/Models/` (`User.php`, `Project.php`, `Bid.php`).
   - Query manual `mysqli` diganti total menggunakan Eloquent ORM.

3. **Controller & Routing**
   - Alur logic dipisah ke controller di `app/Http/Controllers/`:
     - `AuthController.php`: login, register, logout.
     - `ProjectController.php`: list proyek, pencarian, detail, pasang proyek.
     - `BidController.php`: submit tawaran dan penentuan pemenang.
     - `DashboardController.php`: dashboard user.
     - `AdminController.php`: panel kelola user dan proyek.
   - Route web didefinisikan di `routes/web.php`.

4. **Tampilan (Blade Views)**
   - View menggunakan Blade template di `resources/views/`:
     - Layout utama: `resources/views/layouts/app.blade.php`.
     - Folder `projects/`, `auth/`, `dashboard/`, dan `admin/`.

---

## Cara Menjalankan

1. Masuk ke direktori aplikasi:
   ```bash
   cd laravel-app
   ```

2. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate:fresh --seed
   ```

3. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
   Buka di browser: `http://127.0.0.1:8000`

---

## Akun Demo

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | admin123 |
| Klien | budi@example.com | password123 |
| Developer | dewi@example.com | password123 |

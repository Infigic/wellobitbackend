# Laravel 10 Project Setup

This README provides step-by-step instructions for setting up and running the Laravel 10 project locally after cloning the repository.

---

## 🧾 Prerequisites

Make sure the following are installed on your system:

- PHP >= 8.1
- Composer
- MySQL or other supported database
- Node.js and NPM
- Git

Optional but recommended:

- Laravel Valet or XAMPP/Laragon (for local dev server)
- Redis (if used)
- Docker (if you're using a containerized setup)

---

## 📦 Step-by-Step Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/your-laravel-repo.git
cd your-laravel-repo
```

---

### 2. Install PHP Dependencies

```bash
composer install
```

---

### 3. Install JavaScript Dependencies

```bash
npm install
```

---

### 4. Copy and Configure `.env`

```bash
cp .env.example .env
```

Edit the `.env` file to set your database credentials and other environment-specific settings:

```env
APP_NAME=LaravelApp
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

---

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

### 6. Run Database Migrations

```bash
php artisan migrate
```

> Optionally, you can seed the database if seeders are available:
```bash
php artisan db:seed
```
Great catch! You should include the **storage link** step in the setup instructions so that Laravel can serve uploaded files properly (like user profile images or documents).

Here’s the updated section to add to your README after migrations (Step 6):

---

### 7. Create Storage Symlink

```bash
php artisan storage:link
```

This command creates a symbolic link from `public/storage` to `storage/app/public` so uploaded files can be accessed publicly.

---


### 8. Build Frontend Assets

```bash
npm run dev
```

> For production build:
```bash
npm run build
```

---

### 9. Start the Local Development Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` in your browser.

---

## 🛠 Optional: Run Queue Workers (if applicable)

```bash
php artisan queue:work
```

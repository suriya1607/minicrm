# Mini CRM (Laravel + Vue.js)

## Requirements

### Backend
- PHP >= 8.1
- Laravel 10.x (or your installed version)
- Composer
- MySQL 8.x

### Frontend
- Node.js 20.19+ OR 22.12+
- npm 10+
- Vue 3 (Beta)
- Vite (Beta)
- Tailwind CSS 4.x
- TypeScript 5.9.x

A simple Mini CRM application built using **Laravel (Backend API)** and **Vue.js (Frontend)**.

## 🚀 Features
- User Authentication (Login / Logout)
- Role Management
- User Management
- API Integration
- Vue.js Frontend with Laravel Backend
- CRUD Operations

## 🛠 Tech Stack
- Laravel
- Vue.js
- MySQL
- Vite
- Axios

## 📦 Installation

Clone the repository:

```bash
git clone https://github.com/suriya1607/minicrm.git
## 📦 Backend Setup (Laravel)

Go to backend folder:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

Configure database in .env:
DB_DATABASE=minicrm
DB_USERNAME=root
DB_PASSWORD=

SMTP Used brevo
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=a1febc001@smtp-brevo.com
MAIL_PASSWORD=xsmtpsib-0c9b470d70377526da32d4b200a24ee268e579cb51eb4a7065adf7ef4f342e20-Ejhf96TPz8lJyIZs
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="suriya@handletechlabs.com"
MAIL_FROM_NAME="${APP_NAME}"

php artisan migrate
php artisan serve

## 🗂️ Database Schema

Below is the database structure of the CRM system:

(public/schema/crm-schema.jpg)

Frontend Setup (Vue.js)
cd frontend
npm install
npm run dev

API Connection create .env
VITE_API_URL=http://127.0.0.1:8000/api
VITE_ADMIN_ROLE=1
VITE_USER_ROLE=2
VITE_MANAGER_ROLE=3


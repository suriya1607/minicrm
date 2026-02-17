# Mini CRM (Laravel + Vue.js)

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

php artisan migrate
php artisan serve


Frontend Setup (Vue.js)
cd frontend
npm install
npm run dev

API Connection .env
VITE_API_URL=http://127.0.0.1:8000/api


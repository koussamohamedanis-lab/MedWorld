# 🏥 MedWorld

> A comprehensive telemedicine platform for managing medical cabinets, appointments, and patient consultations.

## 📋 Overview

**MedWorld** is a full-stack web application designed to streamline healthcare operations. It enables seamless collaboration between patients, doctors, administrators, and support staff through an intuitive, role-based interface.

### Key Features

- 👥 **Multi-role system** - SuperAdmin, Cabinet Admin, Doctors, Assistants, and Patients
- 📅 **Appointment Management** - Easy scheduling, modification, and cancellation
- 🏥 **Cabinet Management** - Complete medical cabinet organization
- 📊 **Patient Records** - Comprehensive medical history and documentation
- 💬 **Real-time Notifications** - Instant updates on appointment changes
- ⭐ **Doctor Ratings** - Patient feedback and evaluation system
- 🔐 **Secure Authentication** - JWT-based user authentication

---

## 🏗️ Tech Stack

### Frontend
- **Framework**: [Svelte](https://svelte.dev/) 5.39.5
- **Build Tool**: [Vite](https://vitejs.dev/) 7.1.7
- **Language**: TypeScript 5.9.2
- **UI Components**: Flowbite Svelte, Lucide Icons

### Backend
- **Framework**: [Laravel](https://laravel.com/)
- **Language**: PHP
- **Database**: SQLite (configurable)
- **Authentication**: JWT (Firebase)

---

## 🚀 Quick Start

### Prerequisites
- Node.js 16+ and npm/pnpm
- PHP 8.2+
- Composer
- Git

### Installation

#### 1. Clone the repository

```bash
git clone https://github.com/koussamohamedanis-lab/MedWorld.git
cd MedWorld
```

#### 2. Frontend Setup

```bash
npm install
npm run dev
```

Frontend will be accessible at `http://localhost:5173`

#### 3. Backend Setup

```bash
cd backend-php
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev  # or php artisan serve
```

Backend API runs on `http://localhost:8000`

---

## 🔑 Default Credentials

| Role | Email | Password |
|------|-------|----------|
| SuperAdmin | admin@medworld.com | admin123 |
| Doctor | doctor@medworld.com | doctor123 |
| Patient | patient@medworld.com | patient123 |

**Note**: Additional users should be created by authenticated users with appropriate privileges.

---

## 📁 Project Structure

```
MedWorld/
├── src/                    # Frontend (Svelte)
│   ├── routes/            # Page routes
│   ├── lib/               # Utilities, stores, components
│   └── app.html           # Main HTML
├── backend-php/           # Backend (Laravel)
│   ├── app/               # Application logic
│   ├── routes/            # API routes
│   ├── database/          # Migrations & seeders
│   └── config/            # Configuration files
└── package.json           # Frontend dependencies
```

---

## 🔧 Available Scripts

### Frontend
```bash
npm run dev      # Start development server
npm run build    # Build for production
npm run preview  # Preview production build
npm run check    # TypeScript & Svelte check
```

### Backend
```bash
php artisan serve           # Start development server
php artisan migrate         # Run database migrations
php artisan db:seed        # Seed database with sample data
./vendor/bin/phpunit       # Run tests
```

---

## 👥 User Roles & Features

### 1️⃣ SuperAdmin
- Manage medical cabinets
- Create/modify doctor administrators
- Assign premium packages
- Send global notifications
- System-wide oversight

### 2️⃣ Cabinet Admin (Doctor)
- Manage cabinet staff and schedules
- Create/archive doctors and assistants
- Define booking policies
- Handle premium packages
- View cabinet analytics

### 3️⃣ Doctor
- Manage patient consultations
- Record medical observations & prescriptions
- Update availability
- View patient history
- Accept/modify appointments

### 4️⃣ Assistant
- Schedule appointments
- Register patient attendance
- Manage cabinet calendar
- Handle patient registrations
- Process payments

### 5️⃣ Patient
- Create account and book appointments
- View medical history
- Access prescriptions and records
- Rate doctors
- Manage appointments

---

## 🔒 Security

- JWT authentication for API endpoints
- Role-based access control (RBAC)
- Encrypted password storage
- CORS protection
- Input validation & sanitization

---

## 📚 Documentation

- [API Documentation](./backend-php/API_DOCUMENTATION.md)
- [Backend Setup Guide](./backend-php/BACKEND_README.md)
- [TOPSIS Implementation](./backend-php/TOPSIS_IMPLEMENTATION.md)

---

## 🐛 Testing

### Backend Tests
```bash
cd backend-php
php artisan test
```

---

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 👨‍💻 Team

**Team 12** - ESST Project

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📞 Support

For issues and questions, please open an issue on GitHub.

---

<div align="center">
  <strong>Built with ❤️ for better healthcare management</strong>
</div>

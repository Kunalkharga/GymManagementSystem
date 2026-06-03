# 🏋️ Gym SaaS Management System

A modern **Gym Management SaaS web application** built using PHP, MySQL, HTML, CSS, and JavaScript.  
This system helps gym owners manage members, plans, payments, reports, and notifications in a structured and efficient way.
<p align="center">
  <a href="http://anytimegym.onrender.com/" target="_blank">
    🚀 Live Demo
  </a>
</p>

---

## 🚀 Features

### 👤 Authentication System
- User Registration (`gym-register.php`)
- Member Registration (`register-member.php`)
- Secure Login / Logout
- Session-based access control

---

### 📊 Dashboard
- Admin/User dashboard overview
- Quick stats and navigation

---

### 👥 Member Management
- Add new members
- Edit member details
- Approve new members
- Delete members
- View member profiles
- Renew memberships

---

### 💳 Plans & Payments
- Create and manage membership plans
- Edit and delete plans
- Record payments
- View payment history

---

### 📢 Notifications
- System notifications module
- User alerts and updates

---

### 📄 Reports System
- Generate reports
- Export data (`export.php`)

---

### ⚙️ Settings
- Account settings
- Delete account option

---

## 🛠️ Tech Stack

### Frontend
- HTML5
- CSS3
- JavaScript

### Backend
- PHP (Core PHP)

### Database
- MySQL (gym_saas.sql)

### Tools
- Docker (optional deployment)
- XAMPP / Laragon
- VS Code

---

## 📂 Project Structure

```
gym-saas/
│
├── config.php
├── index.php
├── login.php
├── logout.php
├── gym-register.php
├── register-member.php
│
├── dashboard/
│   └── index.php
│
├── members/
│   ├── add.php
│   ├── edit.php
│   ├── delete.php
│   ├── approve.php
│   ├── index.php
│   ├── profile.php
│   └── renew.php
│
├── plans/
│   ├── add.php
│   ├── edit.php
│   ├── delete.php
│   └── index.php
│
├── payments/
│   ├── index.php
│   └── record.php
│
├── reports/
│   ├── index.php
│   └── export.php
│
├── notifications/
│   └── index.php
│
├── settings/
│   ├── index.php
│   └── delete-account.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
│   └── functions.php
│
├── assets/
│   ├── css/style.css
│   └── js/script.js
│
├── database/
│   └── gym_saas.sql
│
└── Dockerfile
```

---

## 🐳 Docker (Optional)

Build and run using Docker:

```bash
docker build -t gym-saas .
docker run -p 8080:80 gym-saas
```

---

## 📌 Future Improvements

- Email notifications system
- Online payment integration (eSewa / Khalti)
- Attendance tracking
- Trainer module
- Role-based access control (Admin / Staff / Member)

---

## ⭐ Support

If you like this project:
- Give a ⭐ on GitHub
- Fork the repository
- Share with others

---

## 📜 License

This project is open-source and available under the MIT License.

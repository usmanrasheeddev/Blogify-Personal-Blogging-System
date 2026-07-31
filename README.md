<div align="center">

# 🚀 Blogify – Personal Blogging System

<p align="center">
  <b>A modern, lightweight, and full-featured PHP & MySQL / SQLite blogging platform featuring Role-Based Access Control (RBAC), category search filtering, comment management, interactive user dashboards, and serverless Vercel cloud deployment.</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/SQLite-PDO-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/HTML5-Semantic-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-Modern-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/Vercel-Serverless-000000?style=for-the-badge&logo=vercel&logoColor=white" alt="Vercel">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

---

</div>

## 🌟 Key Features

### 🔐 1. Authentication & Role-Based Access Control (RBAC)
- **Granular Roles**: `Admin`, `Author`, `Editor`, and `User`.
- **Password Security**: Native `password_hash()` BCRYPT encryption with auto-migration from legacy plain-text entries upon login.
- **Admin Dashboard**: System-wide content moderation, total statistics overview, and author account management (add/remove authors).

### 📄 2. Content & Media Management
- **Article Publishing**: Create, view, edit, and delete rich blog posts.
- **Media Uploads**: Support for cover image attachments and user profile avatar uploads.
- **Search & Category Filters**: Real-time keyword search bar combined with category dropdown filtering.
- **Pagination**: Built-in server-side pagination for optimal performance.

### 💬 3. Community Engagement
- **Discussion System**: Interactive comment threads on blog articles.
- **Admin Moderation**: Edit and delete comment controls for system administrators.

### ⚡ 4. Smart Dual-Engine Database Architecture
- **Automatic Fallback Engine**: Works out-of-the-box with **MySQL** for traditional web hosts (XAMPP/WAMP) and auto-switches to **SQLite (PDO)** when deployed on serverless environments like **Vercel**.
- **Prepared Statements**: 100% SQL injection proof data access layer.

---

## 🛠️ Technology Stack & Skills

| Layer | Skill / Technology | Description |
| :--- | :--- | :--- |
| **Backend Core** | ![PHP](https://img.shields.io/badge/-PHP-777BB4?style=flat-square&logo=php&logoColor=white) | Modular MVC Front Controller, Session Auth, OOP Database Helpers |
| **Database** | ![MySQL](https://img.shields.io/badge/-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) ![SQLite](https://img.shields.io/badge/-SQLite-003B57?style=flat-square&logo=sqlite&logoColor=white) | MySQL Relational Engine with PDO SQLite Fallback Adapter |
| **Frontend Logic** | ![JavaScript](https://img.shields.io/badge/-JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) | ES6 Fetch API for async authentication & UI interactions |
| **Styling & UI** | ![CSS3](https://img.shields.io/badge/-CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) | Custom CSS Design System, Glassmorphic overlays, Responsive Grid |
| **Markup & SEO** | ![HTML5](https://img.shields.io/badge/-HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) | Semantic HTML5 structure with accessible forms & dynamic meta titles |
| **Deployment** | ![Apache](https://img.shields.io/badge/-Apache-D22128?style=flat-square&logo=apache&logoColor=white) ![Vercel](https://img.shields.io/badge/-Vercel-000000?style=flat-square&logo=vercel&logoColor=white) | XAMPP/WAMP web server or Vercel Serverless Functions (`vercel-php`) |

---

## 📂 Project Architecture

```text
Blogify-Personal-Blogging-System/
├── 📁 actions/                  # Controller Action Handlers (Form & API Processing)
│   ├── auth_actions.php      # Login, Register, & Logout handlers
│   ├── comment_actions.php   # Comment creation, editing, & deletion handlers
│   ├── post_actions.php      # Article CRUD handlers
│   └── profile_actions.php   # Profile bio, avatar, & author management handlers
├── 📁 api/                      # Vercel Serverless Function Wrappers
│   ├── add_post.php
│   └── index.php
├── 📁 config/                   # System Configuration & Database Layer
│   ├── auth.php              # Session authorization & role helper functions
│   └── db.php                # Dual Connection Manager (MySQL & SQLite fallback)
├── 📁 includes/                 # Common Components & Helper Modules
│   ├── components.php        # Reusable UI cards, search bar, & pagination
│   ├── footer.php            # HTML Footer & script initializers
│   ├── functions.php         # Data access layer & repository functions
│   └── header.php            # HTML Head, Google Fonts, & Navigation Bar
├── 📁 uploads/                  # Media Upload Storage
│   ├── profiles/             # User avatar images
│   └── .gitkeep
├── 📁 views/                    # Modular Page View Templates
│   ├── add_post.php          # Article publishing form view
│   ├── edit_post.php         # Article editor view
│   ├── home.php              # Main article feed & category search
│   ├── login.php             # User sign-in view
│   ├── profile.php           # User dashboard & admin control panel
│   ├── register.php          # Account creation view
│   └── view_post.php         # Single post discussion & comment view
├── 📄 database.sql              # Clean DDL Database Schema & Seed Data
├── 📄 index.php                 # Front Controller & Main App Router
├── 📄 README.md                 # Professional Showcase Documentation
├── 📄 script.js                 # Client-side Interactive JavaScript
├── 📄 style.css                 # Modern CSS Design System & Theme Styles
└── 📄 vercel.json               # Vercel Serverless Configuration
```

---

## 🔑 Pre-Seeded Demo Accounts

The project includes ready-to-use sample accounts in `database.sql` for instant testing:

| Role | Username / Email | Password | Permissions & Capabilities |
| :--- | :--- | :--- | :--- |
| 👑 **Admin** | `Admin` / `admin@example.com` | `1234` | System-wide statistics, author management, global post & comment moderation |
| ✏️ **Editor** | `Editor` / `editor@example.com` | `1234` | Edit and manage all published articles & comments |
| 📝 **Author** | `Author` / `author@example.com` | `1234` | Write, edit, and publish personal articles & upload cover images |
| 👤 **User** | `User` / `user@example.com` | `1234` | Read articles, filter categories, and participate in discussion comments |

---

## ⚡ Quick Start & Setup Guide

### 💻 Local Server Setup (XAMPP / WAMP / MAMP)

1. **Clone Repository**:
   ```bash
   git clone https://github.com/usmanrasheeddev/Blogify-Personal-Blogging-System.git
   ```
2. **Move to Web Root**:
   Place project inside your local web server root directory (e.g. `C:/xampp/htdocs/Blogify-Personal-Blogging-System`).

3. **Database Import**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`).
   - Create a new database named `simple_blog`.
   - Import `database.sql` into the database.

4. **Run Application**:
   Navigate to `http://localhost/Blogify-Personal-Blogging-System` in your browser.

---

### ☁️ Cloud Deployment (Vercel Serverless)

This codebase includes native `vercel.json` configuration and automatically falls back to an embedded SQLite database in serverless environments.

1. Deploy directly using the Vercel CLI:
   ```bash
   vercel --prod
   ```
2. Alternatively, connect this repository to your Vercel Dashboard for instant continuous deployment.

---

## 🛡️ Security Best Practices

- **SQL Injection Prevention**: Prepared statements (`my_db_prepare()`) are enforced across all database queries.
- **XSS Sanitization**: All user-submitted text fields are escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Password Security**: Uses BCRYPT hashing via PHP `password_hash()` and `password_verify()`.

---

## 📜 License

Distributed under the **MIT License**. See `LICENSE` for more information.

<div align="center">
  <sub>Built with ❤️ by <b>Usman Rasheed</b></sub>
</div>

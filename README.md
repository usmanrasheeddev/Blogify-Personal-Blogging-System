# Blogify – Personal Blogging System

A lightweight PHP and MySQL blogging system with roles, posts, comments, and profile management.

## Features
- Role-based access (Admin, Author)
- Create, edit, delete posts
- Comments on posts
- Profile dashboard with image upload and bio
- Category filtering
- Image uploads for posts

## Tech Stack
- PHP (mysqli)
- MySQL
- HTML/CSS
- JavaScript (fetch for auth)

## Requirements
- PHP 7.4+ (recommended)
- MySQL 5.7+ (recommended)
- Apache (XAMPP/WAMP/LAMP)

## Setup (Local)
1. Place the project in your web root (e.g., `htdocs`).
2. Create a database in phpMyAdmin.
3. Import `database.sql` into the new database.
4. Update DB credentials in `db.php`.
5. Ensure these folders are writable:
   - `uploads/`
   - `uploads/profiles/`

## Run
Open in browser:
- `http://localhost/blog_project/`

## Default Accounts
- Admin: name/email `Admin`, password `1234`
- Author: name/email `Author`, password `1234`

## Notes
- This project stores passwords in plain text for demo purposes only.
- For production, use password hashing and environment-based configuration.

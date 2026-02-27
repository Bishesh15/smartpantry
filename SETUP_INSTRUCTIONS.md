# Quick Setup Instructions

## Step 1: Database Setup

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `smartpantry`
3. Click on the `smartpantry` database
4. Go to "Import" tab
5. Choose file: `database/schema.sql`
6. Click "Go" to import

## Step 2: Access the Application

### Main Application URL:
**http://localhost/smartpantry/**

### Direct URLs:
- **User Login:** http://localhost/smartpantry/views/user/login.php
- **User Registration:** http://localhost/smartpantry/views/user/register.php
- **Admin Login:** http://localhost/smartpantry/views/admin/login.php

## Step 3: Default Credentials

### Admin Account:
- Username: `admin`
- Password: `admin123`

### Test User:
- Create a new account via registration page

## Step 4: Verify Database Connection

If you see database errors, update `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'smartpantry';
private $username = 'root';  // Your MySQL username
private $password = '';       // Your MySQL password (usually empty for XAMPP)
```

## Troubleshooting

1. **404 Error:** Make sure XAMPP Apache is running
2. **Database Error:** Import the schema.sql file first
3. **Password Hashing Error:** bcrypt.js library is already downloaded
4. **Image Upload Error:** Check that `assets/images/` folders have write permissions

## Start Using!

1. Go to: **http://localhost/smartpantry/**
2. Register a new account or login as admin
3. Start exploring recipes!


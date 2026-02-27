# SmartPantry

A modern recipe discovery platform that helps users find, save, and cook healthy meals based on ingredients they already have. Built with PHP/MySQL on XAMPP.

## Features

### User Features
- **Landing page** with ingredient search and popular tags
- **Recipe search** with sidebar filters (category, dietary, cuisine)
- **Recipe detail** pages with ingredients, nutrition, step-by-step instructions, ratings & reviews
- **User dashboard** with stats, saved recipes, recently viewed, and explore cuisines
- **Profile settings** — change username and password
- **Dietary preferences** — manage food preferences and dietary restrictions/allergies
- **Favorites & history** — save recipes and track viewing history
- **Rating system** (1–5 stars) with user reviews
- **Contact page** with feedback form and FAQ
- **Google OAuth** login support
- **Double password hashing** — bcrypt client-side + `password_hash()` server-side

### Admin Features
- Admin dashboard with statistics
- CRUD operations for recipes and ingredients
- User feedback management
- Recipe and ingredient image uploads

## Page Architecture

| Page | File | CSS | Layout |
|------|------|-----|--------|
| Landing | `views/user/home.php` | `landing.css` | Standalone |
| Recipe Search | `views/user/recipe-search.php` | `landing.css` + `recipe-search.css` | Standalone |
| Recipe Detail | `views/user/recipe-detail.php` | `landing.css` + `recipe-detail.css` | Standalone |
| Dashboard | `views/user/dashboard.php` | `landing.css` + `dashboard.css` | Standalone |
| Contact | `views/user/contact.php` | `landing.css` + `contact.css` | Standalone |
| Login / Register | `views/user/login.php` / `register.php` | `auth.css` | Standalone |
| Admin pages | `views/admin/*.php` | `admin.css` | Shared header |

All user-facing pages share the same navbar from `landing.css` with a green (#22c55e) theme and Inter font.

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP (Apache + MySQL) on Windows

### Setup

1. **Clone / copy** the project into `C:\xampp\htdocs\smartpantry\`

2. **Database setup**
   - Create a MySQL database named `smartpantry`
   - Import the schema:
     ```
     mysql -u root -p smartpantry < schema.sql
     ```
   - Or use phpMyAdmin to import `schema.sql`

3. **Configuration**
   - Update database credentials in `config/database.php` if needed
   - Update `BASE_URL` in `config/constants.php` if your path differs:
     ```php
     define('BASE_URL', 'http://localhost/smartpantry/');
     ```

4. **Google OAuth** (optional)
   - Copy `config/secrets.example.php` to `config/secrets.php`
   - Fill in your Google OAuth client ID and secret

5. **bcrypt.js**
   - Ensure `assets/js/bcrypt.min.js` contains the actual library
   - Download from: https://github.com/dcodeIO/bcrypt.js

6. **Directory permissions**
   - Ensure `assets/images/recipes/` and `assets/images/ingredients/` are writable

## Default Admin Credentials

- **Username:** `admin`
- **Password:** `admin123`

> Change the admin password after first login.

## Project Structure

```
smartpantry/
├── index.php                 # Entry point (redirects to landing or admin)
├── schema.sql                # Database schema
├── config/
│   ├── constants.php         # BASE_URL, categories, dietary options
│   ├── database.php          # PDO connection singleton
│   ├── secrets.php           # Google OAuth credentials (gitignored)
│   └── secrets.example.php
├── controllers/
│   ├── AuthController.php    # Login, register, logout (AJAX + redirect)
│   ├── GoogleAuthController.php
│   ├── RecipeController.php  # Search, detail actions
│   ├── UserController.php    # Ratings, favorites, preferences, profile
│   └── AdminController.php
├── models/
│   ├── User.php              # Auth, preferences, stats, favorites, history
│   ├── Recipe.php            # Recipe CRUD and search
│   ├── Ingredient.php
│   ├── Rating.php
│   └── Feedback.php
├── views/
│   ├── user/
│   │   ├── home.php          # Landing page
│   │   ├── recipe-search.php # Search results with filters
│   │   ├── recipe-detail.php # Full recipe view
│   │   ├── dashboard.php     # User dashboard & settings
│   │   ├── contact.php       # Contact / feedback form
│   │   ├── login.php
│   │   └── register.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── recipes.php
│   │   ├── ingredients.php
│   │   ├── feedback.php
│   │   └── includes/header.php
│   └── includes/             # Shared partials
├── assets/
│   ├── css/
│   │   ├── landing.css       # Shared nav + landing page
│   │   ├── recipe-search.css
│   │   ├── recipe-detail.css
│   │   ├── dashboard.css
│   │   ├── contact.css
│   │   ├── auth.css
│   │   ├── admin.css
│   │   └── style.css         # Legacy shared styles
│   ├── js/
│   │   ├── bcrypt.min.js     # Client-side password hashing
│   │   ├── main.js
│   │   ├── recipe-matching.js
│   │   └── validation.js
│   └── images/
│       ├── recipes/
│       └── ingredients/
├── includes/
│   ├── functions.php         # CSRF, sanitize, helpers
│   └── session.php
└── api/
    └── auth/
        └── admin-logout-api.php
```

## Usage

1. **Landing page** — Visit `http://localhost/smartpantry/`. Search by ingredients or browse.
2. **Login / Register** — Create an account or log in. Passwords are double-hashed.
3. **Search recipes** — Enter ingredients, apply filters (category, dietary). Results show match percentage.
4. **Recipe detail** — View ingredients, nutrition, instructions. Rate and save to favorites.
5. **Dashboard** — View stats, saved recipes, history. Update profile, password, and dietary preferences.
6. **Contact** — Send feedback or questions via the contact form.
7. **Admin** — Visit `/views/admin/login.php` to manage recipes, ingredients, and feedback.

## Security

- Double password hashing (client-side bcrypt → server-side `password_hash()`)
- PDO prepared statements (SQL injection prevention)
- Input sanitization via `sanitize()` / `validateInteger()` / `validateFloat()`
- CSRF token on all POST forms
- Session-based auth guards (`isLoggedIn()`, `requireAdmin()`)

## Tech Stack

- **Frontend:** HTML, CSS (vanilla), JavaScript (vanilla)
- **Backend:** PHP (no framework)
- **Database:** MySQL via PDO
- **Auth:** bcrypt.js + PHP `password_hash()`, Google OAuth
- **Fonts:** Inter (Google Fonts)
- **Dev environment:** XAMPP on Windows

## License

This project is open source and available for educational purposes.


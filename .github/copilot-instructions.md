# SmartPantry – Copilot Instructions

## Architecture Overview

SmartPantry is a PHP/MySQL XAMPP application with a hand-rolled MVC structure (no framework). It has two isolated user scopes – **regular users** and **admins** – sharing one session but using separate session keys (`user_id` / `admin_id`).

### Page types & routing

| Page type | View pattern | Include chain |
|-----------|-------------|---------------|
| **Landing page** (`views/user/home.php`) | Standalone – own `<html>`, links `css/landing.css` | No shared header/footer |
| **Recipe search** (`views/user/recipe-search.php`) | Standalone – own `<html>`, links `css/landing.css` + `css/recipe-search.css` | No shared header/footer. Requires login. |
| **Recipe detail** (`views/user/recipe-detail.php`) | Standalone – own `<html>`, links `css/landing.css` + `css/recipe-detail.css` | No shared header/footer. Requires login. |
| **Auth pages** (login/register) | Standalone – own `<html>`, links `css/auth.css` | No shared header/footer |
| **Other user pages** (contact) | Uses `views/includes/header.php` → `views/includes/footer.php` wrapper, links `css/style.css` | Requires `$page_title` set before include |
| **Admin pages** | Uses `views/admin/includes/header.php`, links `css/admin.css` | Requires admin session |

There is **no front-controller or router**. `index.php` simply redirects: admins → admin dashboard, everyone else → `views/user/home.php` (landing). Controllers are invoked directly via form `action` attributes pointing to `controllers/*.php`, which include a dispatch block at the bottom of each file (e.g., `switch ($_POST['action'])`).

### Key navigation flow

```
Landing (home.php) ──search──▶ recipe-search.php (requires login)
                    ──login───▶ login.php ──success──▶ recipe-search.php
                    ──logout──▶ home.php (landing)
```

## Database

- **Connection**: Singleton via `getDB()` in `config/database.php` returning a `PDO` instance.
- **Schema**: `schema.sql` at project root. Core tables: `users`, `admins`, `recipes`, `ingredients`, `recipe_ingredients` (M2M), `ratings`, `favorites`, `recent_views`, `feedback`.
- Models in `models/` own queries; there is no ORM. Each model calls `getDB()` in its constructor.
- All queries use **PDO prepared statements** with named parameters (`:param`).

## Security Patterns (project-specific)

- **Double password hashing**: passwords are bcrypt-hashed client-side (`assets/js/bcrypt.min.js`), sent as a 64-char hex string, then **re-hashed server-side** with `password_hash()`. Never handle plaintext passwords.
- **CSRF**: `generateCSRFToken()` / `verifyCSRFToken()` in `includes/functions.php`. Required on all POST forms: `<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">`.
- **Input sanitisation**: always use `sanitize()` for strings and `validateInteger()` / `validateFloat()` for numerics from `includes/functions.php`.
- **Auth guards**: pages that require login start with `if (!isLoggedIn()) { redirect(BASE_URL . 'views/user/login.php'); }`. Admin pages use `requireAdmin()`.

## Conventions & Patterns

- **Redirects after actions**: Controllers do PRG (Post-Redirect-Get). Flash messages go into `$_SESSION['success']` or `$_SESSION['error']`, then `redirect()`.
- **AJAX support**: `AuthController` detects `X-Requested-With: XMLHttpRequest` and returns JSON; otherwise redirects. Pattern: `$this->isAjax` → `jsonResponse()` or `redirect()`.
- **Image paths**: stored as relative paths in DB (e.g., `images/recipes/dal-bhat.jpg`). Displayed via `ASSETS_PATH . 'images/' . $row['image_url']`. Fallback: `images/recipes/default.jpg`.
- **Star ratings**: rendered by `displayStars()` helper in `includes/functions.php` returning HTML with `.star.filled` / `.star.half` classes.
- **Constants**: categories, dietary options, preferences all defined as arrays in `config/constants.php` (e.g., `RECIPE_CATEGORIES`, `DIETARY_RESTRICTIONS`).
- **Google OAuth**: credentials loaded from `config/secrets.php` (gitignored). Template at `config/secrets.example.php`.

## File Naming & CSS

- Each major page type has its **own CSS file** (`landing.css`, `recipe-search.css`, `auth.css`, `style.css`, `admin.css`). Don't add styles for standalone pages into `style.css`.
- Use `Inter` font via Google Fonts on standalone pages; `style.css` pages use the system `Segoe UI` stack.
- Green accent `#22c55e` on landing/auth pages; blue accent `#3b82f6` on recipe-search.

## When Adding New Features

1. **New user page needing login**: guard with `isLoggedIn()` check at top; include `header.php`/`footer.php` or build standalone with own CSS.
2. **New controller action**: add a `case` in the dispatch `switch` at the bottom of the controller file; redirect to `recipe-search.php` (not `home.php`) for recipe-related flows.
3. **New model method**: follow existing pattern – `try/catch PDOException`, `error_log()`, return empty array or null on failure.
4. **New ingredient/recipe category**: add to the constant arrays in `config/constants.php`.

## Dev Environment

- **Stack**: XAMPP (Apache + MySQL + PHP) on Windows. Base URL: `http://localhost/smartpantry/`.
- **No build tools, no composer, no npm.** Plain PHP + vanilla JS. Just place files and reload.
- **DB setup**: import `schema.sql` via phpMyAdmin or CLI. Credentials in `config/database.php`.

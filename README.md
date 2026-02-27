# Recipe Builder Application

A dynamic platform that allows users to find, create, and save recipes based on ingredients they already have.

## Features

### User Features
- User registration and login with password encryption
- Ingredient selection and recipe matching
- Recipe search functionality
- Recipe detail pages with instructions and nutritional information
- Rating system (1-5 stars) with comments
- Save recipes to favorites
- Recently viewed recipes
- User preferences (food type, dietary restrictions)
- Feedback form

### Admin Features
- Admin dashboard with statistics
- CRUD operations for recipes
- CRUD operations for ingredients
- User feedback management
- Recipe and ingredient image uploads

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

### Setup Instructions

1. **Database Setup**
   - Create a MySQL database named `smartpantry`
   - Import the database schema:
     ```sql
     mysql -u root -p smartpantry < database/schema.sql
     ```
   - Or use phpMyAdmin to import `database/schema.sql`

2. **Configuration**
   - Update database credentials in `config/database.php`:
     ```php
     private $host = 'localhost';
     private $db_name = 'smartpantry';
     private $username = 'root';
     private $password = '';
     ```

3. **Base URL Configuration**
   - Update `BASE_URL` in `config/constants.php` if your installation path differs:
     ```php
     define('BASE_URL', 'http://localhost/smartpantry/');
     ```

4. **bcrypt.js Library**
   - Download bcrypt.js from: https://github.com/dcodeIO/bcrypt.js
   - Or use CDN: https://cdn.jsdelivr.net/npm/bcryptjs@2.4.3/dist/bcrypt.min.js
   - Replace `assets/js/bcrypt.min.js` with the actual library file

5. **Directory Permissions**
   - Ensure `assets/images/` directory is writable for image uploads:
     ```bash
     chmod 755 assets/images
     chmod 755 assets/images/recipes
     chmod 755 assets/images/ingredients
     ```

## Default Admin Credentials

- Username: `admin`
- Password: `admin123`

**Important:** Change the admin password after first login!

## Project Structure

```
smartpantry/
├── config/              # Configuration files
├── models/              # Data models
├── controllers/         # Request handlers
├── views/               # View templates
│   ├── user/           # User-facing pages
│   └── admin/          # Admin pages
├── assets/              # Static assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Uploaded images
├── includes/            # Helper functions
├── database/           # Database schema
└── index.php           # Entry point
```

## Usage

1. **Access the Application**
   - Navigate to: `http://localhost/smartpantry/`
   - You will be redirected to login/register page

2. **User Registration**
   - Click "Register" to create an account
   - Fill in username, email, password
   - Select food preferences and dietary restrictions
   - Password is encrypted client-side before submission

3. **Finding Recipes**
   - After login, select ingredients from the sidebar
   - Click "Find Recipes" to see matching recipes
   - Use the search bar to search by recipe name

4. **Recipe Details**
   - Click on any recipe card to view details
   - Rate and comment on recipes
   - Add recipes to favorites

5. **Admin Access**
   - Navigate to: `http://localhost/smartpantry/views/admin/login.php`
   - Login with admin credentials
   - Manage recipes, ingredients, and feedback

## Security Features

- Password hashing (client-side bcrypt + server-side PHP password_hash)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- CSRF token protection
- Session security
- Input validation (client and server-side)

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Password Hashing:** bcrypt.js (client) + PHP password_hash (server)

## Notes

- The application uses double password hashing (client + server) as specified
- Image uploads are stored in `assets/images/` directory
- Recipe calories are automatically calculated from ingredients
- Recipe ratings are averaged and displayed in real-time

## Troubleshooting

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Image Upload Issues**
   - Check directory permissions
   - Verify `assets/images/` directories exist
   - Check PHP upload settings in php.ini

3. **bcrypt.js Not Working**
   - Ensure actual bcrypt.js library is in `assets/js/bcrypt.min.js`
   - Check browser console for errors

## License

This project is open source and available for educational purposes.


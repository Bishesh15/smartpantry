---
name: Recipe Builder Application
overview: Build a complete Recipe Builder Application with user and admin sides, featuring ingredient-based recipe matching, user authentication, ratings, feedback system, and full CRUD operations for recipes and ingredients using PHP, MySQL, and vanilla JavaScript.
todos:
  - id: db-schema
    content: Create database schema file with all tables (users, recipes, ingredients, recipe_ingredients, ratings, favorites, recent_views, feedback, admins) and sample data
    status: completed
  - id: config-setup
    content: Set up configuration files (database.php, constants.php) and helper functions (functions.php, session.php)
    status: completed
  - id: user-model
    content: Create User model with registration, login, and authentication methods
    status: completed
    dependencies:
      - db-schema
      - config-setup
  - id: auth-controller
    content: Implement AuthController with login, register, and logout functionality
    status: completed
    dependencies:
      - user-model
  - id: auth-pages
    content: Create login and registration pages with client-side password hashing (bcrypt.js) and form validation
    status: completed
    dependencies:
      - auth-controller
  - id: core-models
    content: Create Recipe, Ingredient, Rating, and Feedback models with CRUD operations
    status: completed
    dependencies:
      - db-schema
      - config-setup
  - id: recipe-controller
    content: Implement RecipeController with recipe matching algorithm, search, and detail view functionality
    status: completed
    dependencies:
      - core-models
  - id: user-homepage
    content: Build homepage with ingredient selection, search bar, recipe cards, and recently visited recipes
    status: completed
    dependencies:
      - recipe-controller
  - id: recipe-detail
    content: Create recipe detail page with instructions, ratings system, favorites button, and nutritional info
    status: completed
    dependencies:
      - recipe-controller
      - core-models
  - id: admin-dashboard
    content: Build admin dashboard with statistics, recipe management, ingredient management, and feedback management
    status: completed
    dependencies:
      - core-models
  - id: admin-controller
    content: Implement AdminController with CRUD operations for recipes and ingredients
    status: completed
    dependencies:
      - core-models
  - id: frontend-styling
    content: Create CSS files (style.css, admin.css) with responsive design and modern UI
    status: completed
  - id: javascript-functions
    content: Implement JavaScript files for recipe matching, form validation, and dynamic content loading
    status: completed
    dependencies:
      - user-homepage
  - id: security-validation
    content: Add server-side validation, SQL injection prevention, XSS protection, and CSRF tokens
    status: completed
    dependencies:
      - auth-controller
      - recipe-controller
      - admin-controller
  - id: rating-calories
    content: Implement rating calculation algorithm and calories calculation based on ingredients
    status: completed
    dependencies:
      - recipe-detail
      - core-models
---

# Recipe Builder Application - Implementation Plan

## Project Structure

The application will follow an MVC pattern with the following structure:

```
smartpantry/
├── config/
│   ├── database.php          # Database connection configuration
│   └── constants.php         # Application constants
├── models/
│   ├── User.php              # User model (authentication, preferences)
│   ├── Recipe.php            # Recipe model (CRUD operations)
│   ├── Ingredient.php        # Ingredient model
│   ├── Rating.php            # Rating model
│   └── Feedback.php          # Feedback model
├── controllers/
│   ├── AuthController.php    # Login, registration, logout
│   ├── RecipeController.php # Recipe display, search, matching
│   ├── UserController.php   # User preferences, favorites
│   └── AdminController.php  # Admin dashboard, CRUD operations
├── views/
│   ├── user/
│   │   ├── home.php          # Homepage with ingredient selection
│   │   ├── recipe-detail.php # Recipe detail page
│   │   ├── login.php         # Login page
│   │   ├── register.php      # Registration page
│   │   └── contact.php       # Feedback form
│   ├── admin/
│   │   ├── dashboard.php     # Admin dashboard
│   │   ├── recipes.php       # Recipe management
│   │   ├── ingredients.php   # Ingredient management
│   │   └── feedback.php      # User feedback management
│   └── includes/
│       ├── header.php        # Common header
│       ├── footer.php        # Common footer
│       └── nav.php           # Navigation menu
├── assets/
│   ├── css/
│   │   ├── style.css         # Main stylesheet
│   │   └── admin.css         # Admin-specific styles
│   ├── js/
│   │   ├── main.js           # Main JavaScript functions
│   │   ├── recipe-matching.js # Recipe matching algorithm
│   │   ├── validation.js     # Form validation
│   │   └── bcrypt.min.js     # bcrypt.js library
│   └── images/               # Recipe and ingredient images
├── includes/
│   ├── functions.php         # Helper functions
│   └── session.php           # Session management
├── database/
│   └── schema.sql            # Database schema and initial data
└── index.php                 # Entry point (redirects to home)
```

## Database Schema

The database will include the following tables:

1. **users** - User accounts (id, username, email, password_hash, food_preferences, dietary_restrictions, created_at)
2. **recipes** - Recipe information (id, name, description, instructions, prep_time, image_url, calories, category, created_at)
3. **ingredients** - Available ingredients (id, name, category, calories_per_unit, unit, image_url)
4. **recipe_ingredients** - Many-to-many relationship (recipe_id, ingredient_id, quantity)
5. **ratings** - User ratings (id, user_id, recipe_id, rating, comment, created_at)
6. **favorites** - User favorite recipes (user_id, recipe_id, created_at)
7. **recent_views** - Recently viewed recipes (user_id, recipe_id, viewed_at)
8. **feedback** - User feedback (id, user_id, name, email, message, admin_response, created_at)
9. **admins** - Admin accounts (id, username, password_hash, created_at)

## Implementation Phases

### Phase 1: Database & Configuration Setup

- Create database schema file (`database/schema.sql`) with all tables, relationships, and sample data
- Set up database configuration (`config/database.php`) with connection handling
- Create constants file (`config/constants.php`) for app-wide settings
- Implement helper functions (`includes/functions.php`) for common operations

### Phase 2: Authentication System

- Create User model (`models/User.php`) with registration, login, and session management
- Implement AuthController (`controllers/AuthController.php`) for login/register/logout
- Build registration page (`views/user/register.php`) with client-side password hashing (bcrypt.js)
- Build login page (`views/user/login.php`) with form validation
- Implement server-side password hashing (PHP `password_hash()`) for double hashing
- Add session management (`includes/session.php`) using PHP native sessions
- Create authentication middleware to protect routes

### Phase 3: Core Models

- Implement Recipe model (`models/Recipe.php`) with CRUD operations and matching algorithm
- Implement Ingredient model (`models/Ingredient.php`) for ingredient management
- Implement Rating model (`models/Rating.php`) for rating calculations
- Implement Feedback model (`models/Feedback.php`) for feedback management

### Phase 4: User-Side Pages

- **Homepage** (`views/user/home.php`):
  - Ingredient selection interface (categorized list with checkboxes)
  - Search bar for ingredients/recipes
  - Recipe matching algorithm (JavaScript + PHP)
  - Recipe cards display (name, image, time, calories)
  - Recently visited recipes section
- **Recipe Detail Page** (`views/user/recipe-detail.php`):
  - Full recipe instructions
  - Ingredients list with quantities
  - Nutritional information display
  - Rating system (1-5 stars) with comments
  - Save to favorites button
  - Track recent views
- **Contact/Feedback Page** (`views/user/contact.php`):
  - Feedback form (name, email, message)
  - Form validation (client and server-side)

### Phase 5: Recipe Matching Algorithm

- Client-side: JavaScript function to collect selected ingredients and send to backend
- Server-side: PHP algorithm in RecipeController that:
  - Queries recipes containing selected ingredients
  - Filters by user preferences (food type, dietary restrictions)
  - Orders by match score (recipes with more matching ingredients ranked higher)
  - Returns JSON or renders results

### Phase 6: Admin Dashboard

- Admin login page (separate from user login)
- Admin dashboard (`views/admin/dashboard.php`) with statistics:
  - Total users count
  - Total recipes count
  - Total ratings count
  - Total feedback count
- Recipe management (`views/admin/recipes.php`):
  - List all recipes with edit/delete options
  - Add new recipe form (name, description, instructions, prep_time, image, category)
  - Edit existing recipes
  - Assign ingredients to recipes with quantities
- Ingredient management (`views/admin/ingredients.php`):
  - List all ingredients
  - Add new ingredients (name, category, calories_per_unit, unit)
  - Edit/delete ingredients
- Feedback management (`views/admin/feedback.php`):
  - View all user feedback
  - Respond to feedback
  - Mark feedback as resolved

### Phase 7: Frontend Styling & JavaScript

- Create main stylesheet (`assets/css/style.css`) with:
  - Responsive design
  - Recipe card layouts
  - Form styling
  - Navigation menu
  - Color scheme and typography
- Create admin stylesheet (`assets/css/admin.css`) for dashboard styling
- Implement JavaScript functions (`assets/js/main.js`):
  - AJAX calls for dynamic recipe loading
  - Ingredient selection handlers
  - Form validation
- Implement recipe matching JavaScript (`assets/js/recipe-matching.js`)
- Add client-side validation (`assets/js/validation.js`) for forms

### Phase 8: Security & Validation

- Server-side validation in all controllers:
  - Input sanitization using `filter_var()` and `htmlspecialchars()`
  - SQL injection prevention with prepared statements
  - XSS prevention
- Client-side validation:
  - Email format validation (regex)
  - Password strength validation
  - Required field checks
- Session security:
  - Session regeneration on login
  - CSRF token implementation for forms
  - Secure session cookie settings

### Phase 9: Rating & Calories Calculation

- Rating algorithm: Calculate average rating from all user ratings
- Calories calculation: Sum calories from all recipe ingredients based on quantities
- Display ratings on recipe cards and detail pages
- Update ratings in real-time when new ratings are submitted

### Phase 10: User Preferences & Personalization

- Store user food preferences during registration (Nepali, Continental, Indian, etc.)
- Store dietary restrictions (vegetarian, vegan, gluten-free, etc.)
- Filter recipe suggestions based on preferences
- Implement favorites system (save/unsave recipes)

## Key Files to Create

1. **Database**: `database/schema.sql` - Complete database structure
2. **Config**: `config/database.php`, `config/constants.php`
3. **Models**: All model files in `models/` directory
4. **Controllers**: All controller files in `controllers/` directory
5. **Views**: All view files in `views/` directory
6. **Assets**: CSS and JavaScript files
7. **Entry Point**: `index.php` - Routes to appropriate pages

## Technical Considerations

- **Password Hashing**: Client-side bcrypt.js hashing + server-side PHP `password_hash()` (double hashing as requested)
- **Session Management**: PHP native sessions with secure configuration
- **Form Submissions**: Traditional form POST requests with page reloads
- **Recipe Matching**: SQL queries with JOIN operations on recipe_ingredients table
- **Calories Calculation**: Server-side calculation summing ingredient calories × quantities
- **Rating System**: Average calculation stored and updated in recipes table

## Data Flow Example: Recipe Matching

```
User selects ingredients → JavaScript collects selections → Form POST to RecipeController 
→ Recipe model queries database → Filters by user preferences → Calculates match scores 
→ Returns recipes → Displays in recipe cards on homepage
```

This plan provides a complete, production-ready Recipe Builder Application with all specified features.
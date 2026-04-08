# 📘 Project Defense Guide: SmartPantry Kitchen Assistant

This document provides a complete technical deep-dive into the SmartPantry project. It is designed to help you answer any questions from external examiners, teachers, or collaborators about how the system was built, why certain technologies were chosen, and how the logic works.

---

## 🏗️ 1. Technology Stack & Architecture

### **Core Stack (XAMPP Environment)**
*   **PHP (8.x):** Used for all server-side logic, session management, and database interactions. It was chosen for its mature ecosystem and native integration with MySQL.
*   **MySQL:** Our relational database. It handles complex relationships between users, ingredients, and recipes (Many-to-Many).
*   **Apache:** The web server that handles HTTP requests and serves our PHP files.
*   **JavaScript (Vanilla):** Used for the Interactive UI (AJAX recipe matching, substitutions, and Cooking Focus Mode). We avoided heavy frameworks to keep the site extremely fast and lightweight.
*   **Bootstrap 5:** Our CSS framework for a responsive, mobile-first layout.

### **Architectural Pattern**
We follow a **Modular MVC-lite** architecture:
*   **Models (`/models/`):** Handle data retrieval and logic (e.g., `Recipe.php`).
*   **Views (`/views/`):** The UI components that the user sees.
*   **Controllers (`/controllers/`):** The "brain" that connects the View to the Model (e.g., `AuthController.php`).
*   **Assets (`/assets/`):** Centralized CSS, JS, and Images for better maintainability.

---

## 🧠 2. Core Algorithms Explained

### **A. Smart Matching Algorithm (The "Heart" of SmartPantry)**
*   **Where:** `models/Recipe.php` -> `getMatchingRecipes()`
*   **Concept:** **Hybrid Weighted Scoring.**
*   **How it works:**
    1.  **Exact Matching:** The system takes the list of ingredient IDs you selected and queries the `recipe_ingredients` table.
    2.  **Match Score (50% weight):** It calculates a ratio: `(Selected Ingredients That Match Recipe) / (Total Ingredients in Recipe)`. 
    3.  **User Preference Score (20% weight):** If the recipe category (e.g., "Nepali", "Italian") matches the user's saved preferences, the score gets a boost.
    4.  **Similarity Score (30% weight):** A proxy score used to refine ranking based on common ingredient groupings.
*   **Outcome:** Recipes are ranked by this "Hybrid Score," ensuring you see what you *can* cook first, followed by what you *want* to cook.

### **B. Recommendation Engine (Relative Recipes)**
*   **Where:** `models/Recipe.php` -> `getSimilarRecipes()`
*   **Algorithm:** **Jaccard Similarity Coefficient.**
*   **Formula:** `J(A, B) = |A ∩ B| / |A ∪ B|`
    *   It calculates the intersection (shared ingredients) over the union (total unique ingredients) between two recipes.
    *   **Logic:** If Recipe A and Recipe B share 80% of their ingredients, they are "Similar."
*   **Purpose:** To suggest recipes the user might like based on what they are currently viewing.

### **C. Nutritional Auto-Calculator**
*   **Where:** `models/Recipe.php` -> `calculateCalories()` / SQL Triggers
*   **Logic:** The system iterates through every ingredient in a recipe, fetches its `calories_per_unit` from the `ingredients` table, and multiplies it by the `quantity` defined in the `recipe_ingredients` mapping. 
*   **Benefit:** This allows the "Calorie Counter" on the UI to update in real-time if a user changes the serving size.

---

## 🛡️ 3. Security, Validation & Persistence

### **SQL Injection Prevention**
*   **Strategy:** **PDO (PHP Data Objects) and Prepared Statements.**
*   **Why:** We never inject variables directly into SQL strings. We use placeholders (`?`) and bind values. This makes it impossible for an attacker to run malicious SQL commands.

### **CSRF (Cross-Site Request Forgery)**
*   **Protection:** Every form (Login, Register, Contact) includes a hidden `csrf_token`.
*   **Logic:** The server generates a random string and stores it in the user's `$_SESSION`. When the form is submitted, the server checks if the submitted token matches the session token. If they don't match, the request is rejected.

### **Data Sanitization**
*   **Where:** `includes/functions.php` -> `sanitize()`
*   **Logic:** We use `htmlspecialchars()`, `trim()`, and `stripslashes()` on every piece of user input. This prevents **XSS (Cross-Site Scripting)** attacks.

### **Password Security**
*   **Algorithm:** **Bcrypt.**
*   **Logic:** We use `password_hash()` with a cost factor of 12. We never store plain-text passwords. When a user logs in, we use `password_verify()` to compare the input against the stored hash.

---

## 🛰️ 4. External Integrations (Google Auth)
*   **Technology:** **Google Identity Services (OAuth 2.0).**
*   **Process:**
    1.  **Client-Side:** The Google JS library pops up a login window and returns a secure "Identity Token" (JWT).
    2.  **Server-Side:** Our `GoogleAuthController.php` sends this token to Google's `tokeninfo` API for verification.
    3.  **Account Linking:** If the email matches an existing user, we link the accounts. If not, we auto-create a new profile for the user.

---

## 🛠️ 5. Handling Challenges & Problem Solving

During development, we faced several "real-world" coding problems:

1.  **Broken Image Fallback:** Many external image links would expire. We solved this by implementing an `onerror` handler in HTML that swaps broken images with a high-definition placeholder or a locally stored AI-generated image.
2.  **Dynamic Substitution Logic:** How to suggest a recipe if a user is missing one ingredient? We built a `getIngredientSubstitutes()` helper that maps common kitchen swaps (e.g., Butter -> Oil).
3.  **Complex SQL Queries:** Joining 4+ tables (Users, Pantry, Ingredients, Recipes) was slow for matching. We optimized this by using **SQL GROUP_CONCAT** to fetch all matched IDs in a single database pass.

---

## ❓ 6. Mock Q&A (Prepare for your defense!)

**Q: Why didn't you use a framework like Laravel or React?**
*A: I chose Vanilla PHP and JS to demonstrate a deep understanding of core web principles like session management, manual database relationship handling, and the request-response lifecycle without "magic" abstractions.*

**Q: How do you handle a user having "Extra" ingredients in their pantry?**
*A: The matching algorithm specifically calculates "Available" vs "Required" ingredients. Extra ingredients in the pantry don't penalize the score; they are simply ignored if not needed for the current recipe.*

**Q: Is the site mobile-friendly?**
*A: Yes, I used Bootstrap 5's Grid system and Flexbox utilities. I also built a specific "Cooking Focus Mode" designed for use on tablets and phones in the kitchen, with large touch targets.*

**Q: How is the database structured to handle ingredients?**
*A: We use a Many-to-Many relationship. The `recipe_ingredients` table acts as a bridge, storing not just which ingredients belong to which recipe, but also the specific 'quantity' and 'unit' required for that specific pair.*

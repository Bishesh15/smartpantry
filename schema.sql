-- ============================================================
-- Smart Pantry – A Recipe Recommendation System
-- Complete Database Schema + Sample Data
-- Database: smartpantry
-- 
-- INSTRUCTIONS:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Drop existing 'smartpantry' database if it exists
-- 3. Import this file OR paste into SQL tab and execute
-- ============================================================

DROP DATABASE IF EXISTS smartpantry;
CREATE DATABASE smartpantry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartpantry;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    food_preferences TEXT DEFAULT NULL,
    dietary_restrictions TEXT DEFAULT NULL,
    daily_calorie_goal INT DEFAULT 2000,
    status ENUM('active','deactivated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ingredients
-- ============================================================
CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL,
    calories_per_unit DECIMAL(10, 2) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'gram',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: recipes
-- ============================================================
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    instructions TEXT NOT NULL,
    prep_time INT NOT NULL DEFAULT 30 COMMENT 'Time in minutes',
    servings INT NOT NULL DEFAULT 2,
    diet_type ENUM('Vegetarian','Vegan','Non-Vegetarian') NOT NULL DEFAULT 'Vegetarian',
    category VARCHAR(50) NOT NULL DEFAULT 'Other',
    image_url VARCHAR(500) DEFAULT NULL,
    calories DECIMAL(10, 2) DEFAULT 0,
    average_rating DECIMAL(3, 2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_diet_type (diet_type),
    INDEX idx_name (name),
    INDEX idx_rating (average_rating),
    INDEX idx_views (view_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: recipe_ingredients (Many-to-Many)
-- ============================================================
CREATE TABLE recipe_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL DEFAULT 1,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_recipe_ingredient (recipe_id, ingredient_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_ingredient (ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: pantry_items (user's pantry)
-- ============================================================
CREATE TABLE pantry_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10, 2) DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_ingredient (user_id, ingredient_id),
    INDEX idx_user (user_id),
    INDEX idx_ingredient (ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: favorites
-- ============================================================
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_favorite (user_id, recipe_id),
    INDEX idx_user (user_id),
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: recently_viewed
-- ============================================================
CREATE TABLE recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_recipe_view (user_id, recipe_id),
    INDEX idx_user (user_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ratings
-- ============================================================
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_recipe_rating (user_id, recipe_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: feedback
-- ============================================================
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    admin_response TEXT DEFAULT NULL,
    status ENUM('pending','responded','resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Admin Account
-- Username: admin | Password: admin123
-- ============================================================
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$8K1p/a0dL1LXMIgH0s8GOuDM3kHv0j7F6jVh3nX5bK2YpHRz5S5Wy');

-- ============================================================
-- SEED DATA: Sample Users
-- Password for all: Test@123
-- ============================================================
INSERT INTO users (full_name, username, email, password, food_preferences, dietary_restrictions) VALUES
('Bishesh Shrestha', 'bishesh', 'bishesh@test.com', '$2y$10$8K1p/a0dL1LXMIgH0s8GOuDM3kHv0j7F6jVh3nX5bK2YpHRz5S5Wy', 'Nepali,Indian', 'Vegetarian'),
('Ram Kumar', 'ram_kumar', 'ram@test.com', '$2y$10$8K1p/a0dL1LXMIgH0s8GOuDM3kHv0j7F6jVh3nX5bK2YpHRz5S5Wy', 'Continental,Italian', 'None'),
('Sita Devi', 'sita_devi', 'sita@test.com', '$2y$10$8K1p/a0dL1LXMIgH0s8GOuDM3kHv0j7F6jVh3nX5bK2YpHRz5S5Wy', 'Nepali,Chinese', 'Vegan');

-- ============================================================
-- SEED DATA: Ingredients (35 items across categories)
-- ============================================================
-- Vegetables (1-12)
('Tomato', 'Vegetables', 18.00, 'piece', 'https://images.unsplash.com/photo-1582284540020-8acbe03f4924?q=80&w=400'),
('Onion', 'Vegetables', 40.00, 'piece', 'https://images.unsplash.com/photo-1580201092675-a061eb040082?q=80&w=400'),
('Garlic', 'Vegetables', 4.50, 'clove', 'https://images.unsplash.com/photo-1540148426945-6cf22a6b2383?q=80&w=400'),
('Potato', 'Vegetables', 77.00, 'piece', 'https://images.unsplash.com/photo-1518977676601-b53f02ac10dd?q=80&w=400'),
('Carrot', 'Vegetables', 25.00, 'piece', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?q=80&w=400'),
('Bell Pepper', 'Vegetables', 31.00, 'piece', 'https://images.unsplash.com/photo-1566233228712-ec312f5baad1?q=80&w=400'),
('Spinach', 'Vegetables', 7.00, 'cup', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?q=80&w=400'),
('Cabbage', 'Vegetables', 22.00, 'cup', 'https://images.unsplash.com/photo-1550147760-44c9966d6bc7?q=80&w=400'),
('Cauliflower', 'Vegetables', 25.00, 'cup', 'https://images.unsplash.com/photo-1510627489930-0c1b0ba86699?q=80&w=400'),
('Broccoli', 'Vegetables', 55.00, 'cup', 'https://images.unsplash.com/photo-1452948491233-ad8a1ed00085?q=80&w=400'),
('Green Chili', 'Vegetables', 4.00, 'piece', 'https://images.unsplash.com/photo-1588613481604-4c6020162591?q=80&w=400'),
('Mushroom', 'Vegetables', 15.00, 'cup', 'https://images.unsplash.com/photo-1504305754058-2f08ccd89a0a?q=80&w=400'),

-- Spices & Herbs (13-20)
('Salt', 'Spices', 0.00, 'tsp', NULL),
('Black Pepper', 'Spices', 5.00, 'tsp', NULL),
('Turmeric', 'Spices', 8.00, 'tsp', NULL),
('Cumin', 'Spices', 8.00, 'tsp', NULL),
('Coriander Powder', 'Spices', 5.00, 'tsp', NULL),
('Ginger', 'Spices', 2.00, 'tsp', NULL),
('Chili Powder', 'Spices', 6.00, 'tsp', NULL),
('Cinnamon', 'Spices', 6.00, 'tsp', NULL),

-- Grains & Legumes (21-26)
('Rice', 'Grains', 206.00, 'cup', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?q=80&w=400'),
('Lentils (Dal)', 'Legumes', 230.00, 'cup', 'https://images.unsplash.com/photo-1515942400420-2b98fed1f515?q=80&w=400'),
('Chickpeas', 'Legumes', 269.00, 'cup', 'https://images.unsplash.com/photo-1515543904379-3d757afe72e2?q=80&w=400'),
('Wheat Flour', 'Grains', 455.00, 'cup', NULL),
('Pasta', 'Grains', 220.00, 'cup', 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?q=80&w=400'),
('Bread', 'Grains', 79.00, 'slice', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=400'),

-- Proteins (27-30)
('Chicken', 'Proteins', 231.00, 'cup', 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?q=80&w=400'),
('Egg', 'Proteins', 78.00, 'piece', 'https://images.unsplash.com/photo-1582722872445-44c59ebc41dd?q=80&w=400'),
('Paneer', 'Proteins', 265.00, 'cup', 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?q=80&w=400'),
('Tofu', 'Proteins', 76.00, 'cup', 'https://images.unsplash.com/photo-1615485500704-8e990f3900f1?q=80&w=400'),

-- Dairy (31-33)
('Milk', 'Dairy', 149.00, 'cup', 'https://images.unsplash.com/photo-1563636619-e910f01859ec?q=80&w=400'),
('Butter', 'Dairy', 102.00, 'tbsp', 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?q=80&w=400'),
('Yogurt', 'Dairy', 100.00, 'cup', 'https://images.unsplash.com/photo-1488477181622-b07cfc21bbdf?q=80&w=400'),

-- Oils (34-35)
('Vegetable Oil', 'Oils', 120.00, 'tbsp', NULL),
('Olive Oil', 'Oils', 119.00, 'tbsp', NULL),

-- Extras (36-40)
('Sugar', 'Extras', 49.00, 'tbsp', NULL),
('Lemon', 'Extras', 17.00, 'piece', 'https://images.unsplash.com/photo-1590502593747-42a996133562?q=80&w=400'),
('Soy Sauce', 'Extras', 9.00, 'tbsp', NULL),
('Vinegar', 'Extras', 3.00, 'tbsp', NULL),
('Water', 'Extras', 0.00, 'cup', NULL);

-- ============================================================
-- SEED DATA: Recipes (15 recipes - balanced diet types)
-- ============================================================

-- VEGETARIAN RECIPES (1-5)
INSERT INTO recipes (name, description, instructions, prep_time, servings, diet_type, category, image_url, calories) VALUES
(
    'Dal Bhat (Lentil Rice)',
    'Traditional Nepali comfort food - a wholesome lentil soup served with steamed rice. The most popular everyday meal across Nepal.',
    '1. Wash 1 cup lentils thoroughly and soak for 15 minutes.\n2. In a pressure cooker, add lentils with 3 cups water, 1/2 tsp turmeric, and salt.\n3. Cook for 3 whistles until lentils are soft and mushy.\n4. In a separate pan, heat 2 tbsp oil. Add cumin seeds and let them splutter.\n5. Add finely chopped onion (1 medium) and cook until golden brown.\n6. Add chopped tomatoes (2 medium), ginger-garlic paste, and green chili.\n7. Cook until tomatoes are soft and oil separates.\n8. Add chili powder and coriander powder. Stir for 30 seconds.\n9. Pour the tempering into the cooked lentils and mix well.\n10. Serve hot with steamed rice and a side of vegetables.',
    45, 4, 'Vegetarian', 'Nepali', 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?q=80&w=800', 0
),
(
    'Aloo Gobi (Potato Cauliflower Curry)',
    'A classic Indian vegetarian dish featuring tender potatoes and cauliflower in aromatic spices. Simple yet incredibly flavorful.',
    '1. Cut 2 medium potatoes and 1 small cauliflower into bite-sized pieces.\n2. Heat 2 tbsp oil in a large pan. Add cumin seeds.\n3. Add chopped onion and cook until translucent (3-4 minutes).\n4. Add ginger-garlic paste and cook for 1 minute.\n5. Add chopped tomatoes and cook until soft.\n6. Add turmeric (1/2 tsp), coriander powder (1 tsp), chili powder (1/2 tsp), and salt.\n7. Add potatoes first (they take longer). Stir well and add 1/4 cup water.\n8. Cover and cook for 10 minutes on medium heat.\n9. Add cauliflower pieces. Cover and cook for another 10-12 minutes.\n10. Stir occasionally. Garnish with fresh coriander and serve with roti or rice.',
    35, 3, 'Vegetarian', 'Indian', 'https://images.unsplash.com/photo-1601050690597-df056fb47795?q=80&w=800', 0
),
(
    'Paneer Tikka',
    'Grilled Indian cottage cheese marinated in spiced yogurt. A popular vegetarian appetizer that is smoky, tangy, and absolutely delicious.',
    '1. Cut 250g paneer into 1-inch cubes.\n2. In a bowl, mix 1 cup yogurt, 1 tsp chili powder, 1/2 tsp turmeric, salt, and 1 tbsp oil.\n3. Add paneer cubes to the marinade. Mix gently and refrigerate for 30 minutes.\n4. Cut bell peppers and onions into chunks similar to paneer size.\n5. Thread marinated paneer and vegetables alternately on skewers.\n6. Brush a grill pan with oil and heat on high.\n7. Place skewers on the hot pan. Cook 3-4 minutes per side until charred spots appear.\n8. Alternatively, bake in a preheated oven at 220°C for 15-20 minutes.\n9. Squeeze lemon juice on top.\n10. Serve hot with mint chutney and sliced onion rings.',
    40, 3, 'Vegetarian', 'Indian', 'https://images.unsplash.com/photo-1567184109171-9c17af940173?q=80&w=800', 0
),
(
    'Vegetable Fried Rice',
    'Quick and easy Asian-style fried rice loaded with colorful vegetables. Perfect for using up leftover rice and veggies from your pantry.',
    '1. Cook 2 cups rice and spread on a tray to cool completely (or use day-old rice).\n2. Dice carrots, bell peppers, and onions into small pieces.\n3. Heat 2 tbsp oil in a large wok or pan on HIGH heat.\n4. Add 2 cloves minced garlic and stir for 15 seconds.\n5. Add diced vegetables and stir-fry for 2-3 minutes (keep them crunchy).\n6. Push vegetables to the side. Crack 2 eggs into the pan and scramble.\n7. Add the cold rice. Toss everything together.\n8. Add 2 tbsp soy sauce, 1/2 tsp black pepper, and salt to taste.\n9. Stir-fry on high heat for 2-3 minutes until rice is heated through.\n10. Garnish with spring onions and serve immediately.',
    25, 3, 'Vegetarian', 'Chinese', 'https://images.unsplash.com/photo-1603133872878-622f6fa734b4?q=80&w=800', 0
),
(
    'Pasta Pomodoro (Tomato Pasta)',
    'Classic Italian pasta with fresh tomato sauce. Simple ingredients come together for a restaurant-quality dish in under 25 minutes.',
    '1. Boil a large pot of salted water. Cook 250g pasta until al dente (follow package time minus 1 minute).\n2. While pasta cooks, heat 2 tbsp olive oil in a large pan.\n3. Add 3 cloves sliced garlic. Cook on low heat for 1 minute (do not burn).\n4. Add 4 chopped tomatoes. Cook on medium-high heat for 8-10 minutes until saucy.\n5. Season with salt, black pepper, and a pinch of sugar to balance acidity.\n6. Drain pasta, reserving 1/2 cup pasta water.\n7. Add pasta directly to the sauce pan.\n8. Toss vigorously, adding pasta water as needed to create a glossy coating.\n9. Drizzle with olive oil.\n10. Serve immediately. Top with grated cheese if desired.',
    20, 2, 'Vegetarian', 'Italian', 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?q=80&w=800', 0
),

-- VEGAN RECIPES (6-10)
(
    'Chana Masala (Chickpea Curry)',
    'A hearty and protein-rich vegan curry made with chickpeas in a spiced tomato-onion gravy. A staple across South Asia.',
    '1. Soak 1 cup dried chickpeas overnight, then boil until tender. Or use 2 cans drained chickpeas.\n2. Heat 2 tbsp vegetable oil in a deep pan.\n3. Add cumin seeds (1 tsp) and let them crackle.\n4. Add finely chopped onions (2 medium) and cook until deep golden (8-10 min).\n5. Add ginger-garlic paste (1 tbsp) and green chili. Cook 2 minutes.\n6. Add chopped tomatoes (3 medium). Cook until completely soft and oil separates.\n7. Add turmeric (1/2 tsp), chili powder (1 tsp), coriander powder (1 tsp), and salt.\n8. Add cooked chickpeas and 1 cup water. Mix well.\n9. Simmer on low heat for 15-20 minutes until gravy thickens.\n10. Squeeze lemon juice, garnish with fresh coriander. Serve with rice or bread.',
    50, 4, 'Vegan', 'Indian', 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?q=80&w=800', 0
),
(
    'Stir-Fried Tofu with Vegetables',
    'A protein-packed vegan stir-fry combining crispy tofu with fresh vegetables in a savory soy-ginger sauce.',
    '1. Press 200g tofu between paper towels to remove excess moisture. Cut into cubes.\n2. Heat 1 tbsp oil in a non-stick pan on high heat.\n3. Add tofu cubes in a single layer. Cook undisturbed for 3-4 min until golden on bottom.\n4. Flip and cook another 3 minutes. Remove and set aside.\n5. In same pan, add 1 tbsp oil. Add minced garlic and ginger.\n6. Add sliced bell peppers, broccoli florets, and sliced carrots.\n7. Stir-fry for 3-4 minutes on high heat.\n8. Mix 2 tbsp soy sauce, 1 tsp vinegar, and pinch of sugar in a bowl.\n9. Return tofu to pan. Pour sauce over everything. Toss well.\n10. Serve hot over steamed rice.',
    30, 2, 'Vegan', 'Chinese', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=800', 0
),
(
    'Mixed Vegetable Soup',
    'A nourishing and light vegan soup packed with garden vegetables. Perfect for cold days or when you want something healthy and comforting.',
    '1. Dice 1 potato, 1 carrot, 1 tomato, and 1/4 cabbage into small pieces.\n2. Finely chop 1 onion and 2 cloves garlic.\n3. Heat 1 tbsp olive oil in a large pot.\n4. Add onion and garlic. Sauté until soft (3-4 min).\n5. Add all diced vegetables. Stir for 2 minutes.\n6. Add 4 cups water, salt, black pepper, and turmeric.\n7. Bring to a boil, then reduce heat to low.\n8. Cover and simmer for 20-25 minutes until all vegetables are tender.\n9. For creamy texture, blend half the soup and mix back in. Or serve as is for chunky soup.\n10. Serve hot with a squeeze of lemon and crusty bread.',
    30, 3, 'Vegan', 'Continental', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?q=80&w=800', 0
),
(
    'Garlic Spinach Sauté',
    'A quick, healthy, and incredibly flavorful side dish. Garlic and spinach is a classic combination that comes together in under 10 minutes.',
    '1. Wash 4 cups fresh spinach thoroughly. Drain well.\n2. Peel and thinly slice 4-5 cloves of garlic.\n3. Heat 1 tbsp olive oil in a large pan on medium heat.\n4. Add sliced garlic. Cook for 30-60 seconds until fragrant (do not brown).\n5. Add all the spinach at once. It will wilt down quickly.\n6. Toss with tongs for 1-2 minutes until just wilted.\n7. Season with salt, black pepper, and a pinch of chili flakes if desired.\n8. Squeeze half a lemon over the top.\n9. Transfer to serving plate immediately (do not overcook).\n10. Serve as a side dish or top with toasted bread for a light meal.',
    10, 2, 'Vegan', 'Continental', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=800', 0
),
(
    'Masala Chai (Spiced Tea)',
    'Authentic Nepali/Indian spiced tea with warming spices. A daily essential that is aromatic, comforting, and full of antioxidants.',
    '1. Take 2 cups water in a saucepan.\n2. Add 1 inch crushed ginger and 2 crushed garlic cloves for health benefits.\n3. Add 2 crushed cardamom pods, 1 small cinnamon stick, and 3-4 black peppercorns.\n4. Bring water to a rolling boil. Let spices infuse for 3-4 minutes.\n5. Add 2 tsp black tea leaves (or 2 tea bags). Boil for 2 minutes.\n6. Add 1 cup milk (use plant milk for vegan version).\n7. Bring to boil again, then simmer for 2-3 minutes.\n8. Add sugar to taste (1-2 tsp).\n9. Strain into cups.\n10. Serve hot. Best enjoyed with biscuits or light snacks.',
    15, 2, 'Vegan', 'Nepali', 'https://images.unsplash.com/photo-1561336313-0bd5e0b27ec8?q=80&w=800', 0
),

-- NON-VEGETARIAN RECIPES (11-15)
(
    'Chicken Curry',
    'A rich and aromatic chicken curry with a thick, flavorful gravy. The quintessential home-style chicken dish across South Asia.',
    '1. Cut 500g chicken into medium pieces. Wash and drain.\n2. Marinate chicken with 1/2 cup yogurt, 1/2 tsp turmeric, salt, and 1 tsp chili powder for 30 min.\n3. Heat 3 tbsp oil in a heavy pan. Add cumin seeds and cinnamon stick.\n4. Add 2 finely chopped onions. Cook on medium heat until deep golden brown (10-12 min).\n5. Add ginger-garlic paste (1 tbsp). Cook 2 minutes.\n6. Add 3 chopped tomatoes. Cook until completely soft and oil separates.\n7. Add coriander powder (1 tsp), chili powder (1/2 tsp), and salt.\n8. Add marinated chicken. Sear on high heat for 5 minutes.\n9. Add 1 cup water. Cover and simmer on low heat for 25-30 minutes until chicken is tender.\n10. Garnish with fresh coriander. Serve with rice or naan.',
    60, 4, 'Non-Vegetarian', 'Indian', 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?q=80&w=800', 0
),
(
    'Egg Fried Rice',
    'A quick and satisfying one-pan meal with scrambled egg and fluffy rice. Ready in 15 minutes using basic pantry staples.',
    '1. Cook 2 cups rice and cool completely (day-old rice works best).\n2. Beat 3 eggs with a pinch of salt.\n3. Heat 1 tbsp oil in a wok on HIGH heat.\n4. Pour eggs in. Scramble quickly into small pieces. Remove and set aside.\n5. Add 1 tbsp oil to wok. Add minced garlic (3 cloves).\n6. Add chopped carrots, cook 1 minute.\n7. Add cold rice. Toss and stir-fry for 2-3 minutes.\n8. Add 2 tbsp soy sauce and black pepper.\n9. Return scrambled eggs to wok. Toss everything together.\n10. Serve hot. Optionally top with sliced green onions.',
    15, 2, 'Non-Vegetarian', 'Chinese', 'https://images.unsplash.com/photo-1512058560366-cd24295986fe?q=80&w=800', 0
),
(
    'Chicken Momo (Nepali Dumplings)',
    'Hand-made Nepali steamed dumplings with spiced chicken filling. A beloved street food that is now famous worldwide.',
    '1. DOUGH: Mix 2 cups wheat flour with water and knead into a smooth dough. Rest for 20 min.\n2. FILLING: Mince 300g chicken (or use ground chicken).\n3. Add 1 finely chopped onion, 3 minced garlic cloves, 1 tsp grated ginger.\n4. Add salt, 1/2 tsp black pepper, 1 tsp soy sauce, and 1 tbsp oil. Mix well.\n5. Roll dough into thin circles (about 3 inch diameter).\n6. Place 1 tbsp filling in center of each circle.\n7. Pleat the edges to seal, creating the classic momo shape.\n8. Grease a steamer tray. Place momos without touching each other.\n9. Steam over boiling water for 15-20 minutes until dough becomes translucent.\n10. Serve hot with spicy tomato-sesame chutney (achar).',
    50, 4, 'Non-Vegetarian', 'Nepali', 'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9?q=80&w=800', 0
),
(
    'Omelette',
    'A fluffy, golden omelette that is perfect for breakfast, lunch, or dinner. Highly customizable with whatever vegetables you have.',
    '1. Crack 3 eggs into a bowl. Add salt and black pepper.\n2. Add 1 tbsp milk (optional, makes it fluffier). Beat well with a fork.\n3. Finely chop 1/4 onion, 1/2 tomato, and 1 green chili.\n4. Heat 1 tbsp butter in a non-stick pan on medium heat.\n5. Pour in the beaten eggs. Let them set for 30 seconds without stirring.\n6. Gently push the edges toward center, letting uncooked egg flow to sides.\n7. When top is still slightly wet, add chopped vegetables on one half.\n8. Fold the other half over. Cook 30 seconds more.\n9. Slide onto a plate.\n10. Serve immediately with toast or bread. Add cheese for extra richness.',
    10, 1, 'Non-Vegetarian', 'Continental', 'https://images.unsplash.com/photo-1510629954389-c1e0da47d4ec?q=80&w=800', 0
),
(
    'Chicken Sandwich',
    'A hearty and satisfying chicken sandwich with fresh vegetables. Great for a quick lunch or when you need a filling meal on the go.',
    '1. Season 1 chicken breadt with salt, pepper, and a dash of chili powder.\n2. Heat 1 tbsp oil in a pan on medium-high heat.\n3. Cook chicken breast for 5-6 minutes per side until fully cooked and golden.\n4. Let chicken rest 3 minutes, then slice thinly.\n5. Toast 2 slices of bread until golden.\n6. Spread butter or mayo on both slices.\n7. Layer sliced tomato, onion rings, and lettuce (or spinach) on one slice.\n8. Place sliced chicken on top.\n9. Add salt and pepper to taste.\n10. Close with second bread slice. Cut diagonally and serve.',
    20, 1, 'Non-Vegetarian', 'Continental', 'https://images.unsplash.com/photo-1525059696034-4967a8e1dca2?q=80&w=800', 0
);

-- ============================================================
-- SEED DATA: Recipe Ingredients Mapping
-- ============================================================

-- Recipe 1: Dal Bhat (Lentil Rice)
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(1, 22, 1),    -- 1 cup Lentils
(1, 21, 2),    -- 2 cups Rice
(1, 15, 0.5),  -- 1/2 tsp Turmeric
(1, 13, 1),    -- 1 tsp Salt
(1, 34, 2),    -- 2 tbsp Vegetable Oil
(1, 16, 1),    -- 1 tsp Cumin
(1, 2, 1),     -- 1 Onion
(1, 1, 2),     -- 2 Tomatoes
(1, 18, 1),    -- 1 tsp Ginger
(1, 3, 2),     -- 2 cloves Garlic
(1, 11, 1),    -- 1 Green Chili
(1, 19, 0.5),  -- 1/2 tsp Chili Powder
(1, 17, 1),    -- 1 tsp Coriander Powder
(1, 40, 3);    -- 3 cups Water

-- Recipe 2: Aloo Gobi
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(2, 4, 2),     -- 2 Potatoes
(2, 9, 1),     -- 1 cup Cauliflower
(2, 34, 2),    -- 2 tbsp Oil
(2, 16, 1),    -- 1 tsp Cumin
(2, 2, 1),     -- 1 Onion
(2, 18, 1),    -- 1 tsp Ginger
(2, 3, 2),     -- 2 cloves Garlic
(2, 1, 2),     -- 2 Tomatoes
(2, 15, 0.5),  -- 1/2 tsp Turmeric
(2, 17, 1),    -- 1 tsp Coriander
(2, 19, 0.5),  -- 1/2 tsp Chili Powder
(2, 13, 1);    -- Salt

-- Recipe 3: Paneer Tikka
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(3, 29, 1),    -- 1 cup Paneer
(3, 33, 1),    -- 1 cup Yogurt
(3, 19, 1),    -- 1 tsp Chili Powder
(3, 15, 0.5),  -- 1/2 tsp Turmeric
(3, 13, 1),    -- Salt
(3, 34, 1),    -- 1 tbsp Oil
(3, 6, 1),     -- 1 Bell Pepper
(3, 2, 1),     -- 1 Onion
(3, 37, 1);    -- 1 Lemon

-- Recipe 4: Vegetable Fried Rice
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(4, 21, 2),    -- 2 cups Rice
(4, 5, 1),     -- 1 Carrot
(4, 6, 1),     -- 1 Bell Pepper
(4, 2, 1),     -- 1 Onion
(4, 3, 2),     -- 2 cloves Garlic
(4, 28, 2),    -- 2 Eggs
(4, 34, 2),    -- 2 tbsp Oil
(4, 38, 2),    -- 2 tbsp Soy Sauce
(4, 14, 0.5),  -- Black Pepper
(4, 13, 1);    -- Salt

-- Recipe 5: Pasta Pomodoro
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(5, 25, 1),    -- 1 cup Pasta
(5, 1, 4),     -- 4 Tomatoes
(5, 3, 3),     -- 3 cloves Garlic
(5, 35, 2),    -- 2 tbsp Olive Oil
(5, 13, 1),    -- Salt
(5, 14, 0.5),  -- Black Pepper
(5, 36, 0.5);  -- pinch Sugar

-- Recipe 6: Chana Masala
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(6, 23, 2),    -- 2 cups Chickpeas
(6, 34, 2),    -- 2 tbsp Oil
(6, 16, 1),    -- 1 tsp Cumin
(6, 2, 2),     -- 2 Onions
(6, 18, 1),    -- Ginger
(6, 3, 3),     -- 3 cloves Garlic
(6, 11, 1),    -- Green Chili
(6, 1, 3),     -- 3 Tomatoes
(6, 15, 0.5),  -- Turmeric
(6, 19, 1),    -- Chili Powder
(6, 17, 1),    -- Coriander
(6, 13, 1),    -- Salt
(6, 37, 1),    -- Lemon
(6, 40, 1);    -- Water

-- Recipe 7: Stir-Fried Tofu
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(7, 30, 1),    -- 1 cup Tofu
(7, 34, 2),    -- 2 tbsp Oil
(7, 3, 2),     -- Garlic
(7, 18, 1),    -- Ginger
(7, 6, 1),     -- Bell Pepper
(7, 10, 1),    -- 1 cup Broccoli
(7, 5, 1),     -- Carrot
(7, 38, 2),    -- Soy Sauce
(7, 39, 1),    -- Vinegar
(7, 36, 0.5),  -- Sugar
(7, 13, 1);    -- Salt

-- Recipe 8: Mixed Vegetable Soup
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(8, 4, 1),     -- 1 Potato
(8, 5, 1),     -- 1 Carrot
(8, 1, 1),     -- 1 Tomato
(8, 8, 1),     -- 1 cup Cabbage
(8, 2, 1),     -- 1 Onion
(8, 3, 2),     -- 2 cloves Garlic
(8, 35, 1),    -- 1 tbsp Olive Oil
(8, 13, 1),    -- Salt
(8, 14, 0.5),  -- Black Pepper
(8, 15, 0.5),  -- Turmeric
(8, 40, 4),    -- 4 cups Water
(8, 37, 0.5);  -- half Lemon

-- Recipe 9: Garlic Spinach Sauté
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(9, 7, 4),     -- 4 cups Spinach
(9, 3, 5),     -- 5 cloves Garlic
(9, 35, 1),    -- 1 tbsp Olive Oil
(9, 13, 0.5),  -- Salt
(9, 14, 0.5),  -- Black Pepper
(9, 37, 0.5);  -- half Lemon

-- Recipe 10: Masala Chai
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(10, 40, 2),   -- 2 cups Water
(10, 18, 1),   -- Ginger
(10, 3, 2),    -- 2 cloves Garlic
(10, 20, 1),   -- Cinnamon
(10, 14, 0.5), -- Black Pepper
(10, 31, 1),   -- 1 cup Milk
(10, 36, 2);   -- 2 tbsp Sugar

-- Recipe 11: Chicken Curry
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(11, 27, 2),   -- 2 cups Chicken
(11, 33, 0.5), -- 1/2 cup Yogurt
(11, 15, 0.5), -- Turmeric
(11, 13, 1),   -- Salt
(11, 19, 1),   -- Chili Powder
(11, 34, 3),   -- 3 tbsp Oil
(11, 16, 1),   -- Cumin
(11, 20, 1),   -- Cinnamon
(11, 2, 2),    -- 2 Onions
(11, 18, 1),   -- Ginger
(11, 3, 3),    -- Garlic
(11, 1, 3),    -- 3 Tomatoes
(11, 17, 1),   -- Coriander
(11, 40, 1);   -- Water

-- Recipe 12: Egg Fried Rice
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(12, 21, 2),   -- 2 cups Rice
(12, 28, 3),   -- 3 Eggs
(12, 34, 2),   -- Oil
(12, 3, 3),    -- Garlic
(12, 5, 1),    -- Carrot
(12, 38, 2),   -- Soy Sauce
(12, 14, 0.5), -- Black Pepper
(12, 13, 1);   -- Salt

-- Recipe 13: Chicken Momo
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(13, 24, 2),   -- 2 cups Wheat Flour
(13, 27, 1.5), -- Chicken
(13, 2, 1),    -- Onion
(13, 3, 3),    -- Garlic
(13, 18, 1),   -- Ginger
(13, 13, 1),   -- Salt
(13, 14, 0.5), -- Black Pepper
(13, 38, 1),   -- Soy Sauce
(13, 34, 1),   -- Oil
(13, 40, 1);   -- Water

-- Recipe 14: Omelette
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(14, 28, 3),   -- 3 Eggs
(14, 13, 0.5), -- Salt
(14, 14, 0.5), -- Black Pepper
(14, 31, 1),   -- 1 tbsp Milk (using cup fraction)
(14, 2, 0.25), -- 1/4 Onion
(14, 1, 0.5),  -- 1/2 Tomato
(14, 11, 1),   -- Green Chili
(14, 32, 1);   -- 1 tbsp Butter

-- Recipe 15: Chicken Sandwich
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(15, 27, 1),   -- Chicken
(15, 13, 1),   -- Salt
(15, 14, 0.5), -- Pepper
(15, 19, 0.5), -- Chili Powder
(15, 34, 1),   -- Oil
(15, 26, 2),   -- 2 slices Bread
(15, 32, 1),   -- Butter
(15, 1, 1),    -- Tomato
(15, 2, 0.5),  -- half Onion
(15, 7, 1);    -- Spinach (as lettuce substitute)

-- ============================================================
-- AUTO-CALCULATE: Recipe Calories from Ingredients
-- ============================================================
UPDATE recipes r
SET calories = (
    SELECT COALESCE(SUM(ri.quantity * i.calories_per_unit), 0)
    FROM recipe_ingredients ri
    JOIN ingredients i ON ri.ingredient_id = i.id
    WHERE ri.recipe_id = r.id
);

-- ============================================================
-- SEED DATA: Sample Pantry Items for test users
-- ============================================================
INSERT INTO pantry_items (user_id, ingredient_id, quantity) VALUES
-- User 1 (bishesh) has common Nepali cooking items
(1, 1, 5),    -- Tomato
(1, 2, 3),    -- Onion
(1, 3, 10),   -- Garlic
(1, 4, 4),    -- Potato
(1, 13, 1),   -- Salt
(1, 15, 1),   -- Turmeric
(1, 16, 1),   -- Cumin
(1, 21, 5),   -- Rice
(1, 22, 2),   -- Lentils
(1, 34, 1),   -- Oil
-- User 2 (ram) has continental items
(2, 25, 3),   -- Pasta
(2, 1, 4),    -- Tomato
(2, 3, 5),    -- Garlic
(2, 35, 1),   -- Olive Oil
(2, 28, 6),   -- Eggs
(2, 26, 1),   -- Bread
(2, 32, 1);   -- Butter

-- ============================================================
-- SEED DATA: Sample Favorites
-- ============================================================
INSERT INTO favorites (user_id, recipe_id) VALUES
(1, 1),  -- bishesh likes Dal Bhat
(1, 2),  -- bishesh likes Aloo Gobi
(2, 5),  -- ram likes Pasta
(2, 14); -- ram likes Omelette

-- ============================================================
-- SEED DATA: Sample Ratings
-- ============================================================
INSERT INTO ratings (user_id, recipe_id, rating, comment) VALUES
(1, 1, 5, 'Perfect comfort food! Reminds me of home cooking.'),
(1, 6, 4, 'Great protein source. Added extra lemon for tang.'),
(2, 5, 5, 'So simple yet so good! Made it in 20 minutes.'),
(2, 14, 4, 'My go-to breakfast. Quick and filling.');

-- Update recipe average ratings
UPDATE recipes r
SET 
    average_rating = (
        SELECT COALESCE(AVG(rating), 0) FROM ratings WHERE recipe_id = r.id
    ),
    total_ratings = (
        SELECT COUNT(*) FROM ratings WHERE recipe_id = r.id
    );

-- ============================================================
-- SEED DATA: Sample Recently Viewed
-- ============================================================
INSERT INTO recently_viewed (user_id, recipe_id) VALUES
(1, 1), (1, 2), (1, 6), (1, 10),
(2, 5), (2, 14), (2, 12);

-- ============================================================
-- END OF SCHEMA
-- ============================================================
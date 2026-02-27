-- Recipe Builder Application Database Schema
-- Database: SmartPantryFull

CREATE DATABASE IF NOT EXISTS SmartPantryFull CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE SmartPantryFull;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    google_id VARCHAR(255) DEFAULT NULL,
    food_preferences TEXT,
    dietary_restrictions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ingredients
CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL,
    calories_per_unit DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'gram',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: recipes
CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    instructions TEXT NOT NULL,
    prep_time INT NOT NULL COMMENT 'Time in minutes',
    image_url VARCHAR(255),
    calories DECIMAL(10, 2) DEFAULT 0,
    category VARCHAR(50) NOT NULL,
    average_rating DECIMAL(3, 2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_name (name),
    INDEX idx_rating (average_rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: recipe_ingredients (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_recipe_ingredient (recipe_id, ingredient_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_ingredient (ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ratings
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_recipe_rating (user_id, recipe_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: favorites
CREATE TABLE IF NOT EXISTS favorites (
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

-- Table: recent_views
CREATE TABLE IF NOT EXISTS recent_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: feedback
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    admin_response TEXT,
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, responded, resolved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample admin account (password: admin123 - will be hashed in application)
-- Default admin: username='admin', password='admin123'
INSERT INTO admins (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample ingredients
INSERT INTO ingredients (name, category, calories_per_unit, unit, image_url) VALUES
-- Vegetables
('Tomato', 'Vegetables', 18, 'gram', 'images/ingredients/tomato.jpg'),
('Onion', 'Vegetables', 40, 'gram', 'images/ingredients/onion.jpg'),
('Garlic', 'Vegetables', 149, 'gram', 'images/ingredients/garlic.jpg'),
('Potato', 'Vegetables', 77, 'gram', 'images/ingredients/potato.jpg'),
('Carrot', 'Vegetables', 41, 'gram', 'images/ingredients/carrot.jpg'),
('Bell Pepper', 'Vegetables', 31, 'gram', 'images/ingredients/bell-pepper.jpg'),
('Spinach', 'Vegetables', 23, 'gram', 'images/ingredients/spinach.jpg'),
('Cabbage', 'Vegetables', 25, 'gram', 'images/ingredients/cabbage.jpg'),
('Cauliflower', 'Vegetables', 25, 'gram', 'images/ingredients/cauliflower.jpg'),
('Broccoli', 'Vegetables', 34, 'gram', 'images/ingredients/broccoli.jpg'),

-- Spices & Herbs
('Salt', 'Spices', 0, 'gram', 'images/ingredients/salt.jpg'),
('Black Pepper', 'Spices', 251, 'gram', 'images/ingredients/black-pepper.jpg'),
('Turmeric', 'Spices', 354, 'gram', 'images/ingredients/turmeric.jpg'),
('Cumin', 'Spices', 375, 'gram', 'images/ingredients/cumin.jpg'),
('Coriander', 'Spices', 298, 'gram', 'images/ingredients/coriander.jpg'),
('Ginger', 'Spices', 80, 'gram', 'images/ingredients/ginger.jpg'),
('Chili Powder', 'Spices', 282, 'gram', 'images/ingredients/chili-powder.jpg'),
('Cinnamon', 'Spices', 247, 'gram', 'images/ingredients/cinnamon.jpg'),

-- Grains & Legumes
('Rice', 'Grains', 130, 'gram', 'images/ingredients/rice.jpg'),
('Lentils', 'Legumes', 116, 'gram', 'images/ingredients/lentils.jpg'),
('Chickpeas', 'Legumes', 164, 'gram', 'images/ingredients/chickpeas.jpg'),
('Black Beans', 'Legumes', 132, 'gram', 'images/ingredients/black-beans.jpg'),
('Wheat Flour', 'Grains', 364, 'gram', 'images/ingredients/wheat-flour.jpg'),

-- Proteins
('Chicken', 'Proteins', 165, 'gram', 'images/ingredients/chicken.jpg'),
('Egg', 'Proteins', 155, 'piece', 'images/ingredients/egg.jpg'),
('Paneer', 'Proteins', 295, 'gram', 'images/ingredients/paneer.jpg'),
('Tofu', 'Proteins', 76, 'gram', 'images/ingredients/tofu.jpg'),

-- Dairy
('Milk', 'Dairy', 42, 'ml', 'images/ingredients/milk.jpg'),
('Butter', 'Dairy', 717, 'gram', 'images/ingredients/butter.jpg'),
('Yogurt', 'Dairy', 59, 'gram', 'images/ingredients/yogurt.jpg'),
('Cheese', 'Dairy', 402, 'gram', 'images/ingredients/cheese.jpg'),

-- Oils & Fats
('Vegetable Oil', 'Oils', 884, 'ml', 'images/ingredients/vegetable-oil.jpg'),
('Olive Oil', 'Oils', 884, 'ml', 'images/ingredients/olive-oil.jpg'),
('Ghee', 'Oils', 900, 'gram', 'images/ingredients/ghee.jpg');

-- Insert sample recipes
INSERT INTO recipes (name, description, instructions, prep_time, category, image_url) VALUES
('Dal Bhat', 'Traditional Nepali lentil soup with rice', 
'1. Wash and soak lentils for 30 minutes.\n2. Cook lentils in a pressure cooker with water, turmeric, and salt until soft.\n3. In a separate pan, heat oil and add cumin seeds, garlic, and ginger.\n4. Add chopped onions and tomatoes, cook until soft.\n5. Add chili powder and mix with cooked lentils.\n6. Serve hot with steamed rice and vegetables.',
45, 'Nepali', 'images/recipes/dal-bhat.jpg'),

('Chicken Curry', 'Spicy Indian chicken curry',
'1. Marinate chicken pieces with yogurt, turmeric, and salt for 30 minutes.\n2. Heat oil in a pan and add whole spices (cumin, cinnamon).\n3. Add chopped onions and cook until golden brown.\n4. Add ginger-garlic paste and cook for 2 minutes.\n5. Add tomatoes and cook until oil separates.\n6. Add marinated chicken and cook until tender.\n7. Add water, cover and simmer for 20 minutes.\n8. Garnish with coriander leaves.',
60, 'Indian', 'images/recipes/chicken-curry.jpg'),

('Vegetable Stir Fry', 'Healthy mixed vegetable dish',
'1. Heat oil in a wok or large pan.\n2. Add garlic and ginger, stir for 30 seconds.\n3. Add onions and bell peppers, cook for 2 minutes.\n4. Add remaining vegetables (carrots, broccoli, cauliflower).\n5. Season with salt, pepper, and soy sauce.\n6. Stir fry for 5-7 minutes until vegetables are crisp-tender.\n7. Serve hot.',
20, 'Continental', 'images/recipes/vegetable-stir-fry.jpg'),

('Paneer Tikka', 'Grilled Indian cottage cheese',
'1. Cut paneer into cubes.\n2. Mix yogurt, turmeric, chili powder, and salt.\n3. Marinate paneer in the mixture for 30 minutes.\n4. Thread paneer and vegetables on skewers.\n5. Grill or bake at 200°C for 15-20 minutes.\n6. Serve with mint chutney.',
40, 'Indian', 'images/recipes/paneer-tikka.jpg'),

('Fried Rice', 'Classic Asian-style fried rice',
'1. Cook rice and let it cool completely.\n2. Heat oil in a large pan or wok.\n3. Add chopped garlic and ginger.\n4. Add vegetables (carrots, bell peppers, onions).\n5. Add cooked rice and stir well.\n6. Season with soy sauce, salt, and pepper.\n7. Add scrambled eggs if desired.\n8. Garnish with spring onions.',
25, 'Continental', 'images/recipes/fried-rice.jpg'),

('Momo', 'Nepali steamed dumplings',
'1. Prepare dough with flour and water, let rest.\n2. Mix ground meat or vegetables with spices, onions, and garlic.\n3. Roll dough into small circles.\n4. Place filling in center and fold into dumpling shape.\n5. Steam for 15-20 minutes.\n6. Serve with spicy tomato chutney.',
50, 'Nepali', 'images/recipes/momo.jpg'),

('Aloo Gobi', 'Indian potato and cauliflower curry',
'1. Heat oil in a pan, add cumin seeds.\n2. Add chopped onions and cook until translucent.\n3. Add ginger-garlic paste and tomatoes.\n4. Add turmeric, coriander, and chili powder.\n5. Add potatoes and cauliflower, mix well.\n6. Add water, cover and cook until vegetables are tender.\n7. Garnish with coriander leaves.',
35, 'Indian', 'images/recipes/aloo-gobi.jpg'),

('Pasta with Tomato Sauce', 'Simple Italian-style pasta',
'1. Boil pasta according to package instructions.\n2. Heat olive oil in a pan, add garlic.\n3. Add chopped tomatoes and cook until soft.\n4. Add salt, pepper, and herbs (basil, oregano).\n5. Mix cooked pasta with sauce.\n6. Serve with grated cheese.',
20, 'Continental', 'images/recipes/pasta-tomato.jpg');

-- Link ingredients to recipes (recipe_ingredients)
-- Dal Bhat
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(1, 20, 200), (1, 19, 300), (1, 14, 5), (1, 11, 10), (1, 2, 100), (1, 1, 100), (1, 3, 20), (1, 25, 30);

-- Chicken Curry
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(2, 24, 500), (2, 2, 150), (2, 1, 200), (2, 3, 30), (2, 16, 20), (2, 13, 10), (2, 11, 10), (2, 25, 50), (2, 30, 20);

-- Vegetable Stir Fry
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(3, 6, 200), (3, 2, 100), (3, 5, 150), (3, 10, 200), (3, 9, 200), (3, 3, 20), (3, 25, 40), (3, 11, 5);

-- Paneer Tikka
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(4, 27, 300), (4, 30, 100), (4, 13, 10), (4, 17, 15), (4, 11, 5), (4, 6, 150), (4, 2, 100), (4, 25, 30);

-- Fried Rice
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(5, 19, 300), (5, 25, 50), (5, 2, 100), (5, 5, 100), (5, 6, 150), (5, 3, 15), (5, 11, 5), (5, 25, 2);

-- Momo
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(6, 23, 200), (6, 24, 300), (6, 2, 100), (6, 3, 30), (6, 11, 10), (6, 12, 5), (6, 14, 5), (6, 25, 20);

-- Aloo Gobi
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(7, 4, 300), (7, 9, 300), (7, 2, 100), (7, 1, 100), (7, 3, 20), (7, 14, 5), (7, 13, 10), (7, 15, 5), (7, 25, 40);

-- Pasta with Tomato Sauce
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES
(8, 23, 200), (8, 1, 400), (8, 3, 20), (8, 26, 30), (8, 11, 5), (8, 12, 3), (8, 29, 50);

-- Update recipe calories based on ingredients
UPDATE recipes r
SET calories = (
    SELECT COALESCE(SUM(ri.quantity * i.calories_per_unit), 0)
    FROM recipe_ingredients ri
    JOIN ingredients i ON ri.ingredient_id = i.id
    WHERE ri.recipe_id = r.id
);

-- Migration: Add google_id column if it doesn't exist (for existing databases)
-- Run this if you already have the users table:
-- ALTER TABLE users ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER password_hash;
-- ALTER TABLE users ADD INDEX idx_google_id (google_id);
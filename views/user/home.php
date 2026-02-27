<?php
$page_title = 'Home';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPantry - Cook Healthy, Authentic Meals with What You Have</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navigation -->
<nav class="landing-nav">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="nav-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="4" fill="#22c55e"/>
                <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>SmartPantry</span>
        </a>
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Recipes</a>
            <a href="#how-it-works">About Us</a>
            <a href="<?php echo BASE_URL; ?>views/user/contact.php">Contact Us</a>
        </div>
        <div class="nav-actions">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>views/user/dashboard.php" class="nav-user-icon" title="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="btn-nav-primary">Find Recipes</a>
                <a href="<?php echo BASE_URL; ?>controllers/AuthController.php?action=logout" class="btn-nav-outline">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>views/user/login.php" class="btn-nav-login">Login</a>
                <a href="<?php echo BASE_URL; ?>views/user/register.php" class="btn-nav-primary">Get Started</a>
            <?php endif; ?>
        </div>
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <span class="hero-badge">&#127807; FRESH &amp; LOCAL</span>
            <h1>Cook Healthy,<br><span class="text-green">Authentic Meals</span> with<br>What You Have.</h1>
            <p class="hero-subtitle">Instant recipe recommendations for Nepalese and global cuisines, tailored to your pantry and calorie goals.</p>
            <div class="hero-buttons">
                <a href="#search-section" class="btn-hero-primary">Start Cooking Now</a>
                <a href="#community" class="btn-hero-outline">View Success Stories</a>
            </div>
            <div class="hero-trust">
                <div class="trust-avatars">
                    <div class="avatar" style="background: #f59e0b;">A</div>
                    <div class="avatar" style="background: #3b82f6;">S</div>
                    <div class="avatar" style="background: #ef4444;">D</div>
                </div>
                <span>Trusted by <strong>10,000+</strong> home cooks</span>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-recipe-card">
                <img src="<?php echo ASSETS_PATH; ?>images/recipes/default.jpg" alt="Featured Recipe" class="hero-recipe-img">
                <div class="hero-recipe-overlay">
                    <div class="hero-recipe-info">
                        <span class="hero-recipe-name">Chicken Momos</span>
                        <span class="hero-recipe-sub">Authentic Nepalese Style</span>
                    </div>
                    <div class="hero-recipe-cal">
                        <span class="cal-number">320 kcal</span>
                        <div class="cal-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section" id="search-section">
    <div class="search-container">
        <h2>What's in your kitchen today?</h2>
        <p>Don't know what to cook? Enter 3 ingredients and let us handle the rest.</p>
        <form action="<?php echo BASE_URL; ?>views/user/recipe-search.php" method="GET" class="landing-search-form">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="ingredients" placeholder="Enter ingredients (e.g., Chicken, Rice, Tomatoes)" class="landing-search-input">
                <button type="submit" class="landing-search-btn">Find Recipes</button>
            </div>
        </form>
        <div class="popular-tags">
            <span>Popular:</span>
            <button type="button" class="tag-btn" data-ingredient="Rice">Rice +</button>
            <button type="button" class="tag-btn" data-ingredient="Lentils">Lentils +</button>
            <button type="button" class="tag-btn" data-ingredient="Spinach">Spinach +</button>
            <button type="button" class="tag-btn" data-ingredient="Chicken">Chicken +</button>
            <button type="button" class="tag-btn" data-ingredient="Garlic">Garlic +</button>
        </div>
    </div>
</section>

<!-- Why Choose Section -->
<section class="why-section" id="features">
    <div class="section-container">
        <h2>Why choose SmartPantry?</h2>
        <p class="section-subtitle">We combine traditional cooking with modern health tracking to help you eat better.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon" style="background: #f0fdf4;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                        <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>
                    </svg>
                </div>
                <h3>Smart Pantry</h3>
                <p>Stop wasting food. Input what you already have in your fridge and get instant, delicious suggestions.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background: #f0fdf4;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <h3>Health First</h3>
                <p>Automatic calorie and macro tracking for every meal. Stay on top of your fitness goals effortlessly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background: #f0fdf4;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                </div>
                <h3>Global Flavors</h3>
                <p>From the mountains of Nepal to kitchens around the world. Explore diverse, authentic cuisines.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-section" id="how-it-works">
    <div class="section-container">
        <div class="how-grid">
            <div class="how-images">
                <div class="how-img-grid">
                    <img src="<?php echo ASSETS_PATH; ?>images/recipes/default.jpg" alt="Food" class="how-img">
                    <img src="<?php echo ASSETS_PATH; ?>images/recipes/default.jpg" alt="Food" class="how-img">
                    <img src="<?php echo ASSETS_PATH; ?>images/recipes/default.jpg" alt="Food" class="how-img">
                    <img src="<?php echo ASSETS_PATH; ?>images/recipes/default.jpg" alt="Food" class="how-img">
                </div>
            </div>
            <div class="how-content">
                <h2>How it works</h2>
                <div class="how-steps">
                    <div class="how-step">
                        <div class="step-number">1</div>
                        <div class="step-info">
                            <h3>Add Your Ingredients</h3>
                            <p>Simply type in what you have in your pantry or fridge. No more grocery runs for one missing spice.</p>
                        </div>
                    </div>
                    <div class="how-step">
                        <div class="step-number">2</div>
                        <div class="step-info">
                            <h3>Get Customized Recipes</h3>
                            <p>Our engine suggests authentic recipes based on your inventory. Filter by cuisine, time, or difficulty.</p>
                        </div>
                    </div>
                    <div class="how-step">
                        <div class="step-number">3</div>
                        <div class="step-info">
                            <h3>Track Your Nutrition</h3>
                            <p>See calories and macros for every serving instantly. Eat healthy without the complex math.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section -->
<section class="community-section" id="community">
    <div class="section-container">
        <h2>What our community says</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <p>"Finally found a way to make healthy Dal Bhat that fits my diet! The calorie counting feature is a game changer for my fitness journey."</p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background: #f59e0b;">A</div>
                    <div>
                        <strong>Aarav S.</strong>
                        <span>Kathmandu, Nepal</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <p>"I had so many random ingredients in my pantry. SmartPantry helped me create a delicious dinner without going to the store. Highly recommend!"</p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background: #ec4899;">S</div>
                    <div>
                        <strong>Sarah M.</strong>
                        <span>London, UK</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <p>"The user interface is so clean and easy to use. I love that I can filter for high-protein meals specifically."</p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background: #3b82f6;">D</div>
                    <div>
                        <strong>David K.</strong>
                        <span>Sydney, Australia</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="section-container">
        <div class="cta-card">
            <h2>Ready to cook something amazing?</h2>
            <p>Join thousands of foodies cooking healthy, authentic meals today. It's free to get started.</p>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="btn-cta">Find Recipes Now</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>views/user/register.php" class="btn-cta">Get Started for Free</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="landing-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect width="24" height="24" rx="4" fill="#22c55e"/>
                        <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>SmartPantry</span>
                </div>
                <p>Making healthy cooking accessible, fun, and personalized for everyone, everywhere.</p>
            </div>
            <div class="footer-links">
                <h4>Product</h4>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Recipes</a>
                <a href="#features">Nutrition Tracker</a>
                <a href="#how-it-works">How It Works</a>
            </div>
            <div class="footer-links">
                <h4>Company</h4>
                <a href="#how-it-works">About Us</a>
                <a href="<?php echo BASE_URL; ?>views/user/contact.php">Contact</a>
            </div>
            <div class="footer-newsletter">
                <h4>Stay Updated</h4>
                <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thanks for subscribing!');">
                    <input type="email" placeholder="Email address" required>
                    <button type="submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> SmartPantry. All rights reserved.</p>
            <div class="footer-social">
                <a href="#" aria-label="Twitter">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
                </a>
                <a href="#" aria-label="Instagram">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
// Popular tag buttons - add to search input
document.querySelectorAll('.tag-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = document.querySelector('.landing-search-input');
        const current = input.value.trim();
        const ingredient = this.dataset.ingredient;
        
        if (current) {
            if (!current.toLowerCase().includes(ingredient.toLowerCase())) {
                input.value = current + ', ' + ingredient;
            }
        } else {
            input.value = ingredient;
        }
        input.focus();
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Mobile menu toggle
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('show');
        document.querySelector('.nav-actions').classList.toggle('show');
    });
}
</script>

</body>
</html>


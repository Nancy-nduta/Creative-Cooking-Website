<?php
// Start the session at the very beginning of the file
session_start();


$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- FETCH LATEST RECIPES (LIMIT 4) ---
$latest_recipes_query = "SELECT recipeid, recipename, rmedia, recipecategory FROM recipes ORDER BY recipeid DESC LIMIT 4";
$latest_recipes_result = $conn->query($latest_recipes_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Culinary Compass</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>

    <!-- Embedded Stylesheet -->
    <style>
        /* ==========================================================================
           1. VARIABLES & GLOBAL STYLES (CONSISTENT THEME)
           ========================================================================== */
        :root {
            --primary-color: #83a87d; /* Soft green */
            --primary-color-dark: #6a8d65;
            --secondary-color: #f2eee8; /* Creamy background */
            --text-color: #4a4a4a;
            --heading-color: #2d2d2d;
            --border-color: #e0dcd5;
            --light-color: #ffffff;
            --shadow-color: rgba(0, 0, 0, 0.07);
            --font-body: 'Poppins', sans-serif;
            --font-display: 'Lora', serif;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font-body); background-color: var(--light-color); color: var(--text-color); line-height: 1.7; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
        img { max-width: 100%; display: block; }
        h1, h2, h3, h4 { font-family: var(--font-display); color: var(--heading-color); line-height: 1.3; }
        .section-padding { padding: 5rem 1.5rem; }
        .bg-cream { background-color: var(--secondary-color); }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-header h2 { font-size: 2.75rem; }
        .section-header p { max-width: 600px; margin: 0.5rem auto 0; color: var(--text-color); }
        
        /* ==========================================================================
           2. HEADER, NAVIGATION & HERO
           ========================================================================== */
        .main-header { position: relative; color: var(--light-color); }
        .hero-background {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('hero.jpg') no-repeat center center/cover;
            z-index: 1;
        }
        .navbar { position: relative; z-index: 10; padding: 1rem 0; }
        .navbar .container { display: flex; justify-content: space-between; align-items: center; }
        .navbar-logo img { width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--light-color); }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-menu a { color: var(--light-color); text-decoration: none; font-weight: 500; transition: opacity 0.3s; }
        .nav-menu a:hover { opacity: 0.8; }
        .btn { text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; display: inline-block; transition: all 0.3s ease; border: 1px solid var(--light-color); }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); color: var(--light-color); }
        .btn-primary:hover { background-color: var(--primary-color-dark); border-color: var(--primary-color-dark); }
        .btn-outline { background-color: transparent; color: var(--light-color); }
        .btn-outline:hover { background-color: var(--light-color); color: var(--heading-color); }
        .user-info { display: flex; align-items: center; gap: 0.5rem; }
        
        .hero { position: relative; z-index: 2; min-height: 90vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 0 1rem; }
        .hero-content { max-width: 800px; }
        .hero-title { font-size: 4rem; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5); margin-bottom: 1rem; }
        .hero-subtitle { font-size: 1.25rem; margin-bottom: 2rem; }
        
        /* ==========================================================================
           3. RECIPE & FEATURE CARDS (CONSISTENT STYLING)
           ========================================================================== */
        .grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }
        .recipe-card, .feature-card {
            background-color: var(--light-color);
            border-radius: 12px;
            box-shadow: 0 5px 20px var(--shadow-color);
            overflow: hidden;
            text-decoration: none;
            color: var(--text-color);
            display: flex; flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .recipe-card:hover, .feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .recipe-card img, .feature-card img { width: 100%; height: 220px; object-fit: cover; }
        .card-body { padding: 1.5rem; flex-grow: 1; }
        .card-body h3 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .card-body p { font-size: 0.95rem; }

        /* ==========================================================================
           4. TESTIMONIALS SECTION
           ========================================================================== */
        .testimonial-card { text-align: center; padding: 2rem; }
        .testimonial-card blockquote { font-family: var(--font-display); font-size: 1.25rem; font-style: italic; margin-bottom: 1.5rem; }
        .testimonial-author h4 { font-family: var(--font-body); font-size: 1rem; font-weight: 600; }
        .ratings .fa { color: #fabb05; }
        
        /* ==========================================================================
           5. CTA & FOOTER
           ========================================================================== */
        .cta-section { background-color: var(--primary-color); color: var(--light-color); text-align: center; }
        .cta-section h2 { color: var(--light-color); }
        .main-footer { background-color: var(--heading-color); color: #ccc; text-align: center; padding: 4rem 1.5rem; }
        .main-footer h4 { color: var(--light-color); margin-bottom: 1rem; font-size: 1.5rem; }
        .social-icons { margin: 1.5rem 0; }
        .social-icons a { color: var(--light-color); font-size: 1.5rem; margin: 0 0.75rem; transition: color 0.3s; }
        .social-icons a:hover { color: var(--primary-color); }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu { display: none; } /* Simplified for example; requires JS for hamburger */
            .hero-title { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <!-- ============================ HEADER & NAVIGATION ============================ -->
    <header class="main-header" id="home">
        <div class="hero-background"></div>
        <nav class="navbar">
            <div class="container">
                <a href="/index" class="navbar-logo"><img src="logo 1.jpeg" alt="Culinary Compass Logo"></a>
                <ul class="nav-menu" id="navLinks">
                    <li><a href="/userl">Home</a></li>
                    <li><a href="#features">About</a></li>
                    <li><a href="/our-recipes">Recipes</a></li>
                    <li><a href="/contact-us">Contact</a></li>
                    
                    <!-- PHP Session Logic for User Display -->
                    <li>
                        <?php if (isset($_SESSION['username'])): ?>
                            <?php
                                $username = htmlspecialchars($_SESSION['username']);
                                $user_type = $_SESSION['usertype'] ?? 'user';
                                $link = '#'; // Default
                                if ($user_type == 'admin') $link = '/admin';
                                elseif ($user_type == 'recipeowner') $link = '/recipeowner';
                                elseif ($user_type == 'user') $link = '/user';
                            ?>
                            <a href="<?= $link ?>" class="user-info btn btn-outline">
                                <i class="fa fa-user"></i>&nbsp; <?= $username ?>
                            </a>
                        <?php else: ?>
                            <a href="/loginpage" class="btn btn-primary">Login / Sign Up</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="hero">
            <div class="hero-content">
                <h1 class="hero-title">Discover Delicious Recipes</h1>
                <p class="hero-subtitle">Immerse yourself in a one-of-a-kind experience as you uncover new flavors to spice up your meals.</p>
                <a href="/our-recipes" class="btn btn-primary btn-lg">Explore Recipes</a>
            </div>
        </div>
    </header>

    <!-- ============================ MAIN CONTENT ============================ -->
    <main>

        <!-- LATEST RECIPES SECTION -->
        <section id="latest-recipes" class="section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Latest Recipes</h2>
                    <p>Fresh from our kitchen to yours. Try out these new creations!</p>
                </div>
                <div class="grid-layout">
                    <?php if ($latest_recipes_result && $latest_recipes_result->num_rows > 0): ?>
                        <?php while ($row = $latest_recipes_result->fetch_assoc()): ?>
                            <a href="/viewingrecipe?id=<?= $row['recipeid'] ?>" class="recipe-card">
                                <img src="<?= !empty($row['rmedia']) ? htmlspecialchars($row['rmedia']) : 'https://placehold.co/400x300/f2eee8/ccc?text=Recipe' ?>" alt="<?= htmlspecialchars($row['recipename']) ?>">
                                <div class="card-body">
                                    <h3><?= htmlspecialchars($row['recipename']) ?></h3>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No recent recipes found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION -->
        <section id="features" class="section-padding bg-cream">
            <div class="container">
                <div class="section-header">
                    <h2>Special Features</h2>
                    <p>Tools and content designed to make your cooking journey easier and more enjoyable.</p>
                </div>
                <div class="grid-layout">
                    <div class="feature-card">
                        <img src="mealplanner.png" alt="Meal Planner">
                        <div class="card-body">
                            <h3>Interactive Meal Planner</h3>
                            <p>Plan weekly meals effortlessly. Drag and drop recipes, customize serving sizes, and generate shopping lists instantly.</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <img src="bimage1.jpeg" alt="Cooking Videos">
                        <div class="card-body">
                            <h3>Cooking Videos</h3>
                            <p>Enhance your skills with step-by-step video tutorials led by our expert chefs.</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <img src="ingredientssub1.png" alt="Ingredient Substitution">
                        <div class="card-body">
                            <h3>Ingredient Substitution</h3>
                            <p>Never let a missing ingredient stop you. Get instant suggestions for suitable alternatives.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS SECTION -->
        <section class="testimonials section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>What Our Users Say</h2>
                </div>
                <div class="grid-layout">
                    <div class="testimonial-card">
                        <blockquote>"I love Culinary Compass! The recipes are delicious and easy to follow. I always turn to it whenever I need inspiration."</blockquote>
                        <div class="testimonial-author">
                            <h4>Nancy Nduta</h4>
                            <div class="ratings"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <blockquote>"I've been using Culinary Compass for years—it never fails to impress me. It's so easy to recreate restaurant-quality dishes at home."</blockquote>
                        <div class="testimonial-author">
                            <h4>Alfred Mutua</h4>
                            <div class="ratings"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CALL TO ACTION (CTA) SECTION -->
        <section class="cta-section section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Ready to embark on a culinary journey?</h2>
                    <a href="/our-recipes" class="btn btn-outline btn-lg">Explore Our Recipe Collection</a>
                </div>
            </div>
        </section>
    </main>

    <!-- ============================ FOOTER ============================ -->
    <footer class="main-footer">
        <div class="container">
            <h4>About Us</h4>
            <p>Our mission is to inspire creativity in the kitchen by providing delicious recipes, helpful tools, and a supportive community for cooks of all levels.</p>
            <div class="social-icons">
                <a href="#" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                <a href="#" aria-label="Twitter"><i class="fa fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fa fa-linkedin"></i></a>
            </div>
            <p>Made with <i class="fa fa-heart-o"></i> by Nancy Njoroge. &copy; 2025</p>
        </div>
    </footer>

</body>
</html>

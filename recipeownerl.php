<?php
// Start the session at the very beginning of the file
session_start();

$is_recipe_owner = (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'recipeowner');
$owner_name = $is_recipe_owner ? htmlspecialchars($_SESSION['username']) : 'Recipe Owner';

// In a live environment, redirect users who are not recipe owners
if (!$is_recipe_owner) {
     // header('Location: /loginpage'); // Uncomment this line for your live site
     // exit;
}

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    // Note: In a production site, you might want a gentler error page here.
    error_log("Database Connection Failed: " . $conn->connect_error);
    $latest_recipes_result = null; 
} else {
    // --- FETCH LATEST RECIPES (LIMIT 4) ---
    // Fetch recipes regardless of owner, as this is a general view/dashboard landing
    $latest_recipes_query = "SELECT recipeid, recipename, rmedia, recipecategory FROM recipes ORDER BY recipeid DESC LIMIT 4";
    $latest_recipes_result = $conn->query($latest_recipes_query);
    
    // Close connection after fetching data
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Culinary Compass | Owner Portal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        /* ==========================================================================
           1. VARIABLES & GLOBAL STYLES (ADOPTING USER PAGE STYLING)
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
            
            --spacing-md: 1rem;
            --spacing-lg: 2rem;
            --section-padding: 5rem 1.5rem; 
            --border-radius: 12px; 
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; font-size: 100%; }
        body {
            font-family: var(--font-body);
            line-height: 1.7;
            background-color: var(--light-color);
            color: var(--text-color);
            overflow-x: hidden;
        }
        body.nav-open { overflow: hidden; }
        a { text-decoration: none; color: var(--primary-color); transition: color 0.3s ease; }
        a:hover { color: var(--primary-color-dark); }
        h1, h2, h3, h4 { font-family: var(--font-display); color: var(--heading-color); line-height: 1.3; font-weight: 600; }
        h1 { font-size: 4rem; } 
        h2 { font-size: 2.75rem; } 
        h3 { font-size: 1.5rem; }
        p { margin-bottom: var(--spacing-md); }
        img { max-width: 100%; display: block; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
        .section-padding { padding: var(--section-padding); }
        .bg-cream { background-color: var(--secondary-color); }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-header h2 { margin-bottom: 0.5rem; }
        .section-header p { max-width: 600px; margin: 0 auto; color: var(--text-color); }
        
        .btn { 
            display: inline-block; padding: 0.75rem 1.5rem; 
            border-radius: 50px;
            font-weight: 600;
            text-align: center; cursor: pointer; transition: all 0.3s ease; 
            border: 1px solid transparent;
        }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); color: var(--light-color); }
        .btn-primary:hover { background-color: var(--primary-color-dark); border-color: var(--primary-color-dark); color: var(--light-color); transform: translateY(-3px); }
        .btn-outline { background-color: transparent; color: var(--light-color); border-color: var(--light-color); } 
        .btn-outline:hover { background-color: var(--light-color); color: var(--heading-color); border-color: var(--light-color); }
        .btn-light { background-color: var(--light-color); color: var(--primary-color); border-color: var(--light-color); }
        .btn-light:hover { background-color: #f0f0f0; color: var(--primary-color-dark); transform: translateY(-3px); }
        .btn-lg { padding: 1rem 2rem; font-size: 1.1rem; }
        
        .card { 
            background: var(--light-color); 
            border-radius: var(--border-radius); 
            overflow: hidden; 
            box-shadow: 0 5px 20px var(--shadow-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            display: flex; flex-direction: column;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card img { width: 100%; height: 220px; object-fit: cover; }
        .card-body { padding: 1.5rem; flex-grow: 1; }
        .card-title { margin-bottom: 0.5rem; }
        .grid-layout { display: grid; gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        
        /* Recipe Owner Specific Welcome Banner */
        .welcome-banner { 
            padding: 10px 0; 
            background-color: var(--primary-color);
            color: var(--light-color); 
            font-weight: 500; 
            text-align: center; 
        }

        /* ==========================================================================
           2. HEADER, NAVIGATION & HERO (ADOPTING USER PAGE STYLING)
           ========================================================================== */
        .main-header { 
            position: relative; 
            color: var(--light-color); 
        }
        .hero-background {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('hero.jpg') no-repeat center center/cover;
            z-index: 1;
        }
        .navbar { 
            position: relative; 
            z-index: 10; 
            background: none;
            backdrop-filter: none;
            padding: 1rem 0; 
            box-shadow: none;
        }
        .navbar .container { display: flex; justify-content: space-between; align-items: center; }
        .navbar-logo img { width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--light-color); }
        .navbar-collapse { display: flex; align-items: center; gap: var(--spacing-lg); }
        .nav-menu { display: flex; align-items: center; list-style: none; gap: var(--spacing-lg); }
        .nav-actions { display: flex; align-items: center; gap: var(--spacing-md); }
        .nav-menu a, .nav-actions a { color: var(--light-color); font-weight: 500; padding: 0.5rem 0; position: relative; transition: opacity 0.3s; }
        .nav-menu a:hover { opacity: 0.8; }
        .nav-menu a:not(.btn):hover::after { width: 0; }
        .nav-menu a::after { content: none; }
        
        .nav-toggle { background: none; border: none; cursor: pointer; padding: 0; z-index: 1100; }
        .nav-toggle .hamburger { display: block; width: 25px; height: 2px; background-color: var(--light-color); margin: 5px 0; position: relative; transition: transform 0.3s ease-in-out, opacity 0.2s ease-in-out; }
        .hero { position: relative; z-index: 2; min-height: 90vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 0 1rem; }
        .hero-content { max-width: 800px; }
        .hero-title { color: var(--light-color); font-size: 4rem; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5); margin-bottom: var(--spacing-md); }
        .hero-subtitle { font-size: 1.25rem; color: #f0f0f0; text-shadow: none; margin-bottom: var(--spacing-lg); }
        
        /* ==========================================================================
           3. TESTIMONIALS & FOOTER (ADOPTING USER PAGE STYLING)
           ========================================================================== */
        .testimonial-card { text-align: center; padding: 2rem; background-color: var(--light-color); }
        .testimonial-avatar { display: none; }
        .testimonial-card blockquote { font-family: var(--font-display); font-size: 1.25rem; font-style: italic; color: var(--text-color); border: none; padding: 0; margin-bottom: 1.5rem; }
        .testimonial-author h4 { margin-bottom: 0.25rem; font-family: var(--font-body); font-weight: 600; font-size: 1rem; }
        .ratings i { color: #fabb05; }
        .cta-section { background: var(--primary-color); color: var(--light-color); text-align: center; padding: 5rem 1.5rem; }
        .cta-section h2 { color: var(--light-color); margin-bottom: var(--spacing-md); }
        .main-footer { background-color: var(--heading-color); color: #ccc; text-align: center; padding: 4rem 1.5rem; }
        .main-footer h4 { color: var(--light-color); margin-bottom: 1rem; }
        .social-icons { margin: 1.5rem 0; }
        .social-icons a { color: var(--light-color); font-size: 1.5rem; margin: 0 0.75rem; transition: color 0.3s ease, transform 0.3s ease; }
        .social-icons a:hover { color: var(--primary-color); transform: scale(1.2); }
        
        /* ==========================================================================
           4. RESPONSIVE DESIGN
           ========================================================================== */
        @media (max-width: 992px) {
            .nav-toggle { display: block; }
            .navbar-collapse { position: fixed; top: 0; right: -100%; width: 70%; max-width: 320px; height: 100%; background-color: var(--light-color); flex-direction: column; justify-content: center; align-items: center; gap: 2.5rem; transition: right 0.4s cubic-bezier(0.77, 0, 0.175, 1); box-shadow: -5px 0 15px rgba(0,0,0,0.1); }
            .nav-menu, .nav-actions { flex-direction: column; gap: 2rem; }
            .nav-menu a, .nav-actions a { color: var(--heading-color); }
            body.nav-open .navbar-collapse { right: 0; }
            body.nav-open .nav-toggle .hamburger:nth-child(1) { transform: rotate(45deg); top: 7px; }
            body.nav-open .nav-toggle .hamburger:nth-child(2) { opacity: 0; }
            body.nav-open .nav-toggle .hamburger:nth-child(3) { transform: rotate(-45deg); top: -7px; }
        }
        @media (max-width: 768px) {
            :root { --section-padding: 4rem 1.5rem; }
            h1, .hero-title { font-size: 2.5rem; } 
            h2 { font-size: 2rem; }
            .hero-subtitle { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

    <div class="welcome-banner">
        Welcome, Chef <strong><?php echo $owner_name; ?></strong>! You are now a Recipe Owner.
    </div>

    <header class="main-header">
        <div class="hero-background"></div>
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-logo"><img src="logo 1.jpeg" alt="Culinary Compass Logo"></a>
                
                <div class="navbar-collapse">
                    <ul class="nav-menu">
                        <li><a href="/recipeownerl">Home</a></li>
                        <li><a href="/viewrecipes">Manage Recipes</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#testimonials">Reviews</a></li>
                    </ul>
                    <div class="nav-actions">
                        <a href="/logout" class="btn btn-outline">Logout</a>
                    </div>
                </div>
                
                <button class="nav-toggle" aria-label="Toggle navigation">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>
            </div>
        </nav>

        <div class="hero" id="home">
            <div class="hero-content">
                <h1 class="hero-title">Manage Your Culinary Creations</h1> 
                <p class="hero-subtitle">Access your dashboard to add new recipes, edit existing ones, and review your performance.</p>
                <a href="/viewrecipes" class="btn btn-primary btn-lg">Go to Dashboard</a>
            </div>
        </div>
    </header>

    <main>

        <section id="latest-recipes" class="section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Your Latest Recipes</h2> 
                    <p>Quickly review and manage your most recent contributions to the community.</p>
                </div>
                <div class="grid-layout">
                    <?php if ($latest_recipes_result && $latest_recipes_result->num_rows > 0): ?>
                        <?php while ($row = $latest_recipes_result->fetch_assoc()): ?>
                            <a href="/viewingrecipe?id=<?= htmlspecialchars($row['recipeid']) ?>" class="card">
                                <img 
                                    src="<?= !empty($row['rmedia']) ? htmlspecialchars($row['rmedia']) : 'https://placehold.co/400x300/f2eee8/ccc?text=Recipe' ?>" 
                                    alt="<?= htmlspecialchars($row['recipename']) ?>"
                                >
                                <div class="card-body">
                                    <h3 class="card-title"><?= htmlspecialchars($row['recipename']) ?></h3>
                                    <p class="category" style="color:var(--primary-color-dark); font-size:0.9rem;"><?= htmlspecialchars($row['recipecategory']) ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-recipes-found" style="width:100%; text-align:center; padding: 2rem;">No recent recipes found in the database.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="features" class="section-padding bg-cream"> 
            <div class="container">
                <div class="section-header">
                    <h2>Owner Management Tools</h2> 
                    <p>Tools designed to help you efficiently manage your content and engage with your audience.</p> 
                </div>
                <div class="grid-layout">
                    <div class="card"><img src="mealplanner.png" alt="Meal Planner"><div class="card-body"><h3 class="card-title">Interactive Meal Planner</h3><p>Plan your weekly meals effortlessly with our interactive meal planner tool. Simply drag and drop recipes into your calendar, customize serving sizes, and generate a shopping list with just one click.</p></div></div>
                    <div class="card"><img src="bimage1.jpeg" alt="Cooking Videos"><div class="card-body"><h3 class="card-title">Cooking Videos</h3><p>Enhance your cooking skills with step-by-step video tutorials led by our expert chefs.</p></div></div>
                    <div class="card"><img src="ingredientssub1.png" alt="Ingredient Substitution"><div class="card-body"><h3 class="card-title">Ingredient Substitution</h3><p>Never let a missing ingredient stop you. Get instant suggestions for suitable alternatives.</p></div></div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>What your Fans say</h2> 
                </div>
                <div class="grid-layout">
                    <div class="card testimonial-card">
                        <div class="card-body">
                            <blockquote>"I love Culinary Compass! The recipes are delicious and easy to follow. I always turn to it whenever I need inspiration."</blockquote>
                            <div class="testimonial-author">
                                <h4>Nancy Nduta</h4>
                                <div class="ratings"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="card testimonial-card">
                        <div class="card-body">
                            <blockquote>"I've been using Culinary Compass for years—it never fails to impress me. It's so easy to recreate restaurant-quality dishes at home."</blockquote>
                            <div class="testimonial-author">
                                <h4>Alfred Mutua</h4>
                                <div class="ratings"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Ready to publish your next masterpiece?</h2> 
                <a href="/viewrecipes" class="btn btn-light btn-lg">MANAGE RECIPES NOW</a> 
            </div>
        </section>

    </main>

    <footer class="main-footer">
        <div class="container">
            <h4>About Culinary Compass</h4>
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

    <script>
        const navToggle = document.querySelector('.nav-toggle');
        navToggle.addEventListener('click', () => {
            document.body.classList.toggle('nav-open');
        });

        // Close nav when a link is clicked
        const navbarCollapse = document.querySelector('.navbar-collapse');
        navbarCollapse.addEventListener('click', (e) => {
            if (e.target.tagName === 'A') {
                document.body.classList.remove('nav-open');
            }
        });
    </script>
</body>
</html>
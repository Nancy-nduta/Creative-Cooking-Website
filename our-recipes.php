<?php
// Start the session to access user login data.
session_start();

// --- DYNAMIC HOMEPAGE LINK LOGIC ---
// Determine the correct homepage URL based on the user's role.
$home_page_url = '/index'; // Default for unregistered visitors
if (isset($_SESSION['usertype'])) {
    if ($_SESSION['usertype'] === 'recipeowner') {
        $home_page_url = '/recipeownerl'; // Homepage for recipe owners
    } elseif ($_SESSION['usertype'] === 'user') {
        $home_page_url = '/userl'; // Or a specific user dashboard like 'user_dashboard.php'
    }
}

// --- DATABASE CONNECTION ---
$db_host = "sql206.infinityfree.com"; 
$db_username = "if0_40121371";
$db_password = "C4LaekdcxQWrOU"; 
$db_name = "if0_40121371_recipewebsite";
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- HANDLE SEARCH & FILTERING ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$stmt = null; // Initialize $stmt for later closing

// Base SQL query
$query = "SELECT recipeid, recipename, ingredients, steps, recipeowner, rmedia, recipecategory FROM recipes";
$conditions = [];
$params = [];
$types = '';

if (!empty($search_term)) {
    // Search in recipe name or recipe owner name
    $conditions[] = "(recipename LIKE ? OR recipeowner LIKE ?)";
    $params[] = "%" . $search_term . "%";
    $params[] = "%" . $search_term . "%";
    $types .= 'ss';
}

if (!empty($category_filter)) {
    $conditions[] = "recipecategory = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY recipeid DESC";

// Use a prepared statement for security
$stmt = $conn->prepare($query);

// Check if prepare was successful before binding
if ($stmt) {
    if (!empty($params)) {
        // The splat operator (...) unpacks the $params array for bind_param
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If statement preparation failed
    $result = null;
    error_log("Failed to prepare statement: " . $conn->error);
}

// Fetch one recipe to be the "featured" one (Only if no search/filter is active)
$featured_recipe = null;
if (empty($search_term) && empty($category_filter)) {
    $featured_recipe_query = "SELECT * FROM recipes ORDER BY recipeid DESC LIMIT 1";
    $featured_result = $conn->query($featured_recipe_query);
    if ($featured_result) {
        $featured_recipe = $featured_result->fetch_assoc();
    }
}


// Fetch all unique categories for the filter bar
$categories_query = "SELECT DISTINCT recipecategory FROM recipes WHERE recipecategory != '' ORDER BY recipecategory ASC";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes - Culinary Compass</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-body); background-color: var(--secondary-color); color: var(--text-color); line-height: 1.7; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
        img { max-width: 100%; display: block; }
        a { text-decoration: none; color: var(--primary-color); }
        h1, h2, h3 { 
            font-family: var(--font-display); 
            color: var(--heading-color); 
            font-weight: 600; 
        }
        
        /* --- 1. Top Bar (FOR BACK BUTTON) --- */
        .top-bar {
            background-color: var(--primary-color-dark);
            padding: 0.5rem 0;
        }
        .top-bar .container { padding-top: 0; padding-bottom: 0; }
        .back-link {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: opacity 0.3s ease;
            display: inline-block;
            padding: 0.5rem 0; /* Add padding for touch targets */
        }
        .back-link:hover { opacity: 0.8; }
        .back-link .fa { margin-right: 0.5rem; }
        
        /* --- 2. Main Header & Search --- */
        .main-header { background-color: var(--light-color); border-bottom: 1px solid var(--border-color); padding: 1rem 0; }
        .main-header .container { display: flex; justify-content: space-between; align-items: center; }
        .site-logo img { width: 60px; height: 60px; border-radius: 50%; }
        .search-form { display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 50px; overflow: hidden; }
        .search-input { border: none; padding: 0.75rem 1.25rem; font-size: 0.9rem; background: none; width: 250px; }
        .search-input:focus { outline: none; border-color: var(--primary-color); }
        .search-button { 
            border: none; 
            background: none; 
            padding: 0 1.25rem; 
            font-size: 1rem; 
            cursor: pointer; 
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        .search-button:hover {
            color: var(--primary-color-dark);
        }
        
        /* --- 3. Featured Recipe Section --- */
        .featured-recipe-section { padding: 4rem 0; text-align: center; }
        .featured-recipe-card { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 2.5rem; /* Increased gap */
            align-items: center; 
            text-align: left; 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 2.5rem;
            background: var(--light-color);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        .featured-image { 
            border-radius: 8px; 
            height: 400px; 
            object-fit: cover; 
            width: 100%;
            box-shadow: 0 4px 10px var(--shadow-color);
        }
        .featured-content .category { 
            color: var(--primary-color); 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin-bottom: 0.5rem; 
            font-size: 0.9rem;
        }
        .featured-content h1 { 
            font-family: var(--font-display); 
            font-size: 2.75rem; 
            color: var(--heading-color); 
            line-height: 1.2; 
            margin-bottom: 1rem; 
        }
        .featured-content p { margin-bottom: 1.5rem; }
        .btn { 
            text-decoration: none; 
            background-color: var(--primary-color); 
            color: var(--light-color); 
            padding: 0.8rem 1.8rem; 
            border-radius: 50px; 
            font-weight: 600; 
            display: inline-block; 
            transition: all 0.3s ease; 
            border: 1px solid var(--primary-color);
        }
        .btn:hover { 
            background-color: var(--primary-color-dark); 
            border-color: var(--primary-color-dark);
            transform: translateY(-2px);
        }
        
        /* --- 4. Category Filter Bar --- */
        .category-filter { 
            text-align: center; 
            padding: 2rem 0; 
            border-top: 1px solid var(--border-color); 
            border-bottom: 1px solid var(--border-color); 
            background-color: var(--light-color); /* Added background for visibility */
        }
        .category-filter a { 
            text-decoration: none; 
            color: var(--text-color); 
            font-weight: 500; 
            padding: 0.6rem 1.2rem; 
            margin: 0 0.5rem; 
            border-radius: 50px; 
            transition: all 0.3s ease; 
            display: inline-block; 
            margin-bottom: 0.5rem; 
            border: 1px solid transparent;
        }
        .category-filter a:hover { 
            background-color: var(--secondary-color); 
            border-color: var(--border-color);
            color: var(--heading-color);
        }
        .category-filter a.active { 
            background-color: var(--primary-color); 
            color: var(--light-color); 
            border-color: var(--primary-color);
        }
        
        /* --- 5. Recipe Grid --- */
        .recipe-grid-section { padding: 4rem 0; }
        .recipe-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .recipe-card { 
            background-color: var(--light-color); 
            border-radius: 12px; 
            box-shadow: 0 5px 20px var(--shadow-color); 
            overflow: hidden; 
            text-decoration: none; 
            color: var(--text-color); 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .recipe-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        }
        .recipe-card-image { width: 100%; height: 250px; object-fit: cover; }
        .recipe-card-content { padding: 1.5rem; }
        .recipe-card-content .category { 
            color: var(--primary-color); 
            font-weight: 600; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            margin-bottom: 0.5rem; 
        }
        .recipe-card-content h3 { 
            font-family: var(--font-display); 
            font-size: 1.5rem; 
            color: var(--heading-color); 
            line-height: 1.3; 
        }
        .no-recipes-message { 
            text-align: center; 
            padding: 4rem 0; 
            background: var(--light-color);
            border-radius: 12px;
            box-shadow: 0 5px 20px var(--shadow-color);
        }
        
        /* --- Responsive Design --- */
        @media (max-width: 992px) {
            .featured-recipe-card { grid-template-columns: 1fr; text-align: center; }
            .featured-image { height: 300px; }
        }
        @media (max-width: 768px) {
            .main-header .container { flex-direction: column; gap: 1rem; }
            .search-form { width: 100%; max-width: 350px; }
            .search-input { width: 100%; }
            .category-filter { 
                display: flex; 
                overflow-x: auto; 
                justify-content: flex-start; 
                padding: 1.5rem 1rem; 
                white-space: nowrap; 
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch; /* iOS smooth scrolling */
            }
            .category-filter a { flex-shrink: 0; }
        }
    </style>
</head>
<body>
    
    <!-- ======================= TOP BAR (DYNAMIC BACK BUTTON) ======================= -->
    <div class="top-bar">
        <div class="container">
            <a href="<?= $home_page_url ?>" class="back-link"><i class="fa fa-arrow-left"></i> Back to Homepage</a>
        </div>
    </div>

    <!-- ======================= HEADER (LOGO & SEARCH BAR) ======================= -->
    <header class="main-header">
        <div class="container">
            <a href="<?= $home_page_url ?>" class="site-logo">
                <img src="logo 1.jpeg" alt="Culinary Compass Logo">
            </a>
            <form action="/our-recipes" method="GET" class="search-form">
                <input type="search" name="search" class="search-input" placeholder="Search for recipes..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </header>

    <!-- ======================= FEATURED RECIPE (Only shown on non-filtered view) ======================= -->
    <?php if ($featured_recipe && empty($search_term) && empty($category_filter)): ?>
    <section class="featured-recipe-section">
        <div class="container">
            <div class="featured-recipe-card">
                <img src="<?= !empty($featured_recipe['rmedia']) ? htmlspecialchars($featured_recipe['rmedia']) : 'https://placehold.co/600x400/f2eee8/ccc?text=Featured' ?>" alt="<?= htmlspecialchars($featured_recipe['recipename']) ?>" class="featured-image">
                <div class="featured-content">
                    <p class="category"><?= htmlspecialchars($featured_recipe['recipecategory']) ?></p>
                    <h1><?= htmlspecialchars($featured_recipe['recipename']) ?></h1>
                    <p>Our latest and greatest recipe! A delicious dish perfect for any occasion, crafted by **<?= htmlspecialchars($featured_recipe['recipeowner']) ?>**.</p>
                    <a href="viewingrecipe?id=<?= $featured_recipe['recipeid'] ?>" class="btn">View Recipe</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>



    <!-- ======================= RECIPE GRID ======================= -->
    <main class="recipe-grid-section">
        <div class="container">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="recipe-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <a href="/viewingrecipe?id=<?= $row['recipeid'] ?>" class="recipe-card">
                            <img src="<?= !empty($row['rmedia']) ? htmlspecialchars($row['rmedia']) : 'https://placehold.co/400x300/f2eee8/ccc?text=Recipe' ?>" alt="<?= htmlspecialchars($row['recipename']) ?>" class="recipe-card-image">
                            <div class="recipe-card-content">
                                <p class="category"><?= htmlspecialchars($row['recipecategory']) ?></p>
                                <h3><?= htmlspecialchars($row['recipename']) ?></h3>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-recipes-message">
                    <i class="fa fa-cutlery fa-3x" style="color:var(--primary-color-dark); margin-bottom: 1rem;"></i>
                    <h2>No Recipes Found</h2>
                    <?php if (!empty($search_term) || !empty($category_filter)): ?>
                        <p>Sorry, we couldn't find any recipes matching your criteria. Try another search or filter!</p>
                    <?php else: ?>
                        <p>The culinary journey awaits! New recipes will be added soon.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php
    // --- CLOSE DATABASE CONNECTION (Safely check if statements exist) ---
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($categories_result) && $categories_result) {
        $categories_result->close();
    }
    $conn->close();
    ?>
</body>
</html>

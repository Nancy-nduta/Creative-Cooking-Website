<?php
// Start the session at the very beginning
session_start();


$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- GET RECIPE ID AND FETCH DATA ---
$recipe_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$recipe = null;

if ($recipe_id) {
    $query = "SELECT recipeid, recipename, ingredients, steps, recipeowner, rmedia, recipecategory FROM recipes WHERE recipeid = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $recipe = $result->fetch_assoc();
    }
    $stmt->close();
}
$conn->close();

/**
 * Function to format a block of text into an HTML list.
 * FIXED: Wrapped in function_exists() to prevent "Cannot redeclare function" error.
 */
if (!function_exists('format_text_as_list')) {
    function format_text_as_list($text, $list_type = 'ul') {
        $items = explode("\n", trim($text));
        $list_html = "<$list_type>";
        foreach ($items as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $list_html .= "<li>" . htmlspecialchars($item) . "</li>";
            }
        }
        $list_html .= "</$list_type>";
        return $list_html;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $recipe ? htmlspecialchars($recipe['recipename']) : 'Recipe Not Found' ?> - Culinary Compass</title>
    
    <!-- Google Fonts: Lora (Display) and Poppins (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        /* ==========================================================================
           1. VARIABLES & GLOBAL STYLES (ADOPTING USER PAGE STYLING)
           ========================================================================== */
        :root {
            --primary-color: #83a87d; /* Soft green */
            --primary-color-dark: #6a8d65;
            --secondary-color: #f2eee8; /* Creamy background */
            --light-color: #ffffff;
            --bg-color: var(--secondary-color);
            --text-color: #4a4a4a;
            --heading-color: #2d2d2d;
            --border-color: #e0dcd5;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --font-primary: 'Poppins', sans-serif;
            --font-display: 'Lora', serif; /* Changed to Lora */
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-primary);
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.8;
        }
        
        /* ==========================================================================
           2. HEADER & LAYOUT STYLES
           ========================================================================== */
        .site-header {
            padding: 1.5rem 0;
            background-color: var(--light-color);
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }

        .site-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
            box-shadow: 0 4px 15px var(--shadow-color);
            transition: transform 0.3s ease;
        }

        .site-logo:hover {
            transform: scale(1.05);
        }
        
        .container { 
            max-width: 1100px; 
            margin: 0 auto; 
            padding: 3rem 1.5rem; 
        }
        
        .recipe-content {
            background-color: var(--light-color);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08); /* Smoother shadow */
            overflow: hidden;
        }

        .recipe-grid {
            display: grid;
            grid-template-columns: 1fr 1.25fr;
            gap: 0;
        }

        .recipe-image-container { background-color: var(--heading-color); } /* Dark background for contrast */
        .recipe-image { width: 100%; height: 100%; object-fit: cover; display: block; }
        .recipe-details { padding: 3rem; }
        
        .recipe-category {
            color: var(--primary-color-dark); /* Darker green */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .recipe-title {
            font-family: var(--font-display);
            color: var(--heading-color);
            font-size: 2.75rem;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .recipe-owner {
            color: var(--text-color);
            font-weight: 500;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 1rem;
        }

        .recipe-section { margin-bottom: 2.5rem; }
        .recipe-section h3 {
            font-family: var(--font-display);
            color: var(--primary-color-dark);
            font-size: 1.75rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        
        .recipe-section ul, .recipe-section ol { 
            padding-left: 25px; 
            color: var(--text-color);
        }
        .recipe-section li { 
            margin-bottom: 0.75rem; 
            font-weight: 400; 
        }
        .recipe-section ol li {
            font-weight: 500; /* Steps look better bolded */
        }
        
        .back-link-container { text-align: center; margin-top: 2.5rem; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--primary-color);
            border-radius: 50px; /* Rounded button style */
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-link:hover { 
            background-color: var(--primary-color); 
            color: var(--light-color); 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .error-message { text-align: center; padding: 4rem 2rem; }
        .error-message h1 { 
            font-family: var(--font-display);
            color: var(--heading-color);
            font-size: 2.5rem; 
            margin-bottom: 1rem; 
        }

        /* ==========================================================================
           3. RESPONSIVE DESIGN
           ========================================================================== */
        @media (max-width: 992px) {
            .recipe-grid { grid-template-columns: 1fr; }
            .recipe-image { height: 350px; } /* Reduced height for mobile */
            .recipe-details { padding: 2rem; }
            .recipe-title { font-size: 2.25rem; }
            .container { padding: 2rem 1rem; }
        }
    </style>
</head>
<body>

    <!-- ==========================================================================
        SITE HEADER WITH LOGO
        ========================================================================== -->
    <header class="site-header">
        <a href="/our-recipes" title="Back to All Recipes">
            <img src="logo 1.jpeg" alt="Culinary Compass Logo" class="site-logo">
        </a>
    </header>

    <div class="container">
        <?php if ($recipe): ?>
            <main class="recipe-content">
                <div class="recipe-grid">
                    <div class="recipe-image-container">
                        <img src="<?= !empty($recipe['rmedia']) ? htmlspecialchars($recipe['rmedia']) : 'https://placehold.co/800x1200/F2EEE8/4A4A4A?text=Recipe+Image' ?>" alt="<?= htmlspecialchars($recipe['recipename']) ?>" class="recipe-image">
                    </div>

                    <div class="recipe-details">
                        <p class="recipe-category"><?= htmlspecialchars($recipe['recipecategory']) ?></p>
                        <h1 class="recipe-title"><?= htmlspecialchars($recipe['recipename']) ?></h1>
                        <p class="recipe-owner">By **<?= htmlspecialchars($recipe['recipeowner']) ?>**</p>

                        <section class="recipe-section">
                            <h3>Ingredients</h3>
                            <?= format_text_as_list($recipe['ingredients'], 'ul') ?>
                        </section>

                        <section class="recipe-section">
                            <h3>Preparation</h3>
                            <?= format_text_as_list($recipe['steps'], 'ol') ?>
                        </section>
                    </div>
                </div>
            </main>
            
            <div class="back-link-container">
                <a href="/our-recipes" class="back-link">
                    <i class="fa fa-arrow-left"></i> Back to All Recipes
                </a>
            </div>

        <?php else: ?>
            <div class="error-message">
                <h1>Recipe Not Found</h1>
                <p>Sorry, we couldn't find the recipe you're looking for. It may have been removed or the link is incorrect.</p>
                <div class="back-link-container">
                    <a href="/our-recipes" class="back-link">
                        <i class="fa fa-arrow-left"></i> Back to All Recipes
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Start the session to check for a logged-in user.
session_start();

// --- DYNAMIC HOMEPAGE LINK LOGIC ---
// Determine the correct homepage URL based on the user's role.
$home_page_url = '/index'; // Default for guests or normal users
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'recipeowner') {
    $home_page_url = '/recipeownerl'; // Specific homepage for recipe owners
}


$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- CHECK USER SESSION AND PREPARE QUERY ---
$is_owner_logged_in = isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'recipeowner' && isset($_SESSION['username']);
$result = null;

if ($is_owner_logged_in) {
    // Get the username of the currently logged-in recipe owner.
    $current_recipe_owner = $_SESSION['username'];

    // Modify the SQL query to select only recipes belonging to the logged-in owner.
    $query = "SELECT recipeid, recipename, ingredients, steps, recipeowner, rmedia, recipecategory FROM recipes WHERE recipeowner = ? ORDER BY recipeid DESC";
    
    // Prepare, bind, and execute the statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $current_recipe_owner);
    $stmt->execute();
    $result = $stmt->get_result();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage My Recipes</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        /* CSS uses only standard properties, no <canvas> required */
        :root {
            --primary-color: #83a87d; /* Soft green */
            --primary-color-dark: #6a8d65;
            --secondary-color: #f2eee8; /* Creamy background */
            --danger-color: #dc3545; 
            --text-color: #4a4a4a;
            --heading-color: #2d2d2d;
            --light-color: #ffffff;
            --bg-color: var(--secondary-color); 
            --border-color: #e0dcd5;
            --shadow-color: rgba(0, 0, 0, 0.08);

            --font-body: 'Poppins', sans-serif;
            --font-display: 'Lora', serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: var(--font-body); 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            line-height: 1.6; 
        }
        h1, h2, h3 { 
            font-family: var(--font-display); 
            color: var(--heading-color); 
            font-weight: 600; 
        }
        
        /* --- Top Bar for Back Button --- */
        .top-bar {
            background-color: var(--primary-color-dark); 
            padding: 0.5rem 1.5rem;
        }
        .top-bar .container { padding-top: 0; padding-bottom: 0; }
        .back-link {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: opacity 0.3s ease;
        }
        .back-link:hover { opacity: 0.8; }
        .back-link .fa { margin-right: 0.5rem; }

        .container { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }
        img { max-width: 100%; display: block; }
        
        /* --- Header & Title --- */
        .page-header { 
            display: flex; justify-content: space-between; align-items: center; 
            flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem; 
            padding-bottom: 1.5rem; 
            border-bottom: 1px solid var(--border-color); 
        }
        .page-header h1 { 
            font-size: 2.5rem; 
            color: var(--heading-color);
        }
        
        /* --- Buttons --- */
        .btn { 
            display: inline-flex; align-items: center; gap: 0.5rem; 
            padding: 0.75rem 1.5rem; border: none; 
            border-radius: 50px; 
            font-size: 1rem; font-weight: 600; 
            text-decoration: none; cursor: pointer; transition: all 0.3s ease; 
        }
        .btn-primary { 
            background-color: var(--primary-color); 
            color: var(--light-color); 
            border: 1px solid var(--primary-color);
        }
        .btn-primary:hover { 
            background-color: var(--primary-color-dark); 
            border-color: var(--primary-color-dark);
            transform: translateY(-2px); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); 
        }

        /* --- Recipe Cards --- */
        .recipe-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; }
        .recipe-card { 
            background-color: var(--light-color); 
            border-radius: 12px; 
            box-shadow: 0 5px 20px var(--shadow-color); 
            overflow: hidden; 
            display: flex; flex-direction: column; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .recipe-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12); 
        }
        .recipe-card-image { width: 100%; height: 220px; object-fit: cover; }
        .recipe-card-content { padding: 1.5rem; flex-grow: 1; }
        .recipe-card-title { 
            font-size: 1.5rem; 
            margin-bottom: 0.5rem; 
            font-family: var(--font-display); 
            color: var(--heading-color);
        }
        .recipe-meta { color: var(--text-color); font-size: 0.9rem; margin-bottom: 1rem; }
        .recipe-meta .fa { margin-right: 0.3rem; color: var(--primary-color); }
        .recipe-card-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 0 1.5rem 1.5rem; }
        
        /* --- Action Buttons (Edit/Delete) --- */
        .action-btn { 
            padding: 0.5rem 1rem; 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 500; 
            border-radius: 6px; 
            border: 1px solid; 
            background-color: transparent; 
            cursor: pointer; 
            transition: all 0.3s ease; 
        }
        .edit-btn { color: var(--primary-color); border-color: var(--primary-color); }
        .edit-btn:hover { background-color: var(--primary-color); color: var(--light-color); }
        .delete-btn { color: var(--danger-color); border-color: var(--danger-color); }
        .delete-btn:hover { background-color: var(--danger-color); color: var(--light-color); }
        
        /* --- Message Boxes --- */
        .message-box { 
            text-align: center; 
            padding: 4rem 2rem; 
            background-color: var(--light-color); 
            border-radius: 12px; 
            box-shadow: 0 5px 20px var(--shadow-color);
        }
        .message-box h2 { margin-bottom: 0.5rem; font-family: var(--font-display); }
        .message-box .fa { font-size: 3rem; color: var(--danger-color); margin-bottom: 1rem; }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .page-header h1 { margin-bottom: 1rem; }
            .container { padding: 1rem; }
        }
        
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="container">
            <a href="<?= htmlspecialchars($home_page_url) ?>" class="back-link">
                <i class="fa fa-arrow-left"></i> Back 
            </a>
        </div>
    </div>

    <div class="container">
        
        <header class="page-header">
            <h1>Manage My Recipes</h1>
            <a href="/addrecipe" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add New Recipe
            </a>
        </header>
        
        <main>
            <?php if ($is_owner_logged_in): ?>
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="recipe-grid">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="recipe-card">
                                <img src="<?= !empty($row['rmedia']) ? htmlspecialchars($row['rmedia']) : 'https://placehold.co/600x400/F2EEE8/4A4A4A?text=No+Image' ?>" alt="<?= htmlspecialchars($row['recipename']) ?>" class="recipe-card-image">
                                
                                <div class="recipe-card-content">
                                    <h3 class="recipe-card-title"><?= htmlspecialchars($row['recipename']) ?></h3>
                                    <p class="recipe-meta">
                                        <span><i class="fa fa-tag"></i> <?= htmlspecialchars($row['recipecategory']) ?></span>
                                    </p>
                                </div>
                                
                                <div class="recipe-card-actions">
                                    <a href="/editrecipe?id=<?= $row['recipeid'] ?>" class="action-btn edit-btn">
                                        <i class="fa fa-pencil"></i> Edit
                                    </a>
                                    <form action="/deleterecipe" method="post">
                                        <input type="hidden" name="id" value="<?= $row['recipeid'] ?>">
                                        <button type="submit" class="action-btn delete-btn">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="message-box">
                        <h2>No Recipes Found</h2>
                        <p>It looks like you haven't added any recipes yet. Click the button above to get started!</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="message-box">
                    <i class="fa fa-exclamation-triangle"></i>
                    <h2>Access Denied</h2>
                    <p>You must be logged in as a Recipe Owner to view this page.</p>
                </div>
            <?php endif; ?>
        </main>
        
    </div>

    <?php
    // Close the database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    ?>
</body>
</html>
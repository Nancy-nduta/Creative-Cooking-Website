<?php
session_start();

// Connect to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection errors
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// Check if a recipe ID is provided in the URL
if (isset($_GET['id'])) {
	$recipe_id = $_GET['id'];

	// Retrieve recipe data using prepared statements for security
	$sql = "SELECT * FROM recipes WHERE recipeid = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $recipe_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
	} else {
		echo 'Recipe not found';
		exit;
	}

	// Close the prepared statement
	$stmt->close();
} else {
	echo 'No recipe ID provided';
	exit;
}

// Determine the back link URL (always to the view recipes page for owners)
$back_link_url = '/viewrecipes'; 

$conn->close(); // Close the connection (temporarily closed for cleaner structure)
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	<title>Edit Recipe | <?= htmlspecialchars($row['recipename']) ?></title>
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
			--label-color: var(--heading-color);
			--border-color: #e0dcd5; /* Lighter, earthy border */
			--shadow-color: rgba(0, 0, 0, 0.1);
			
			--font-body: 'Poppins', sans-serif;
			--font-display: 'Lora', serif;
		}

		* { box-sizing: border-box; margin: 0; padding: 0; }
		
		body {
			font-family: var(--font-body);
			background-color: var(--bg-color);
			color: var(--text-color);
			line-height: 1.6;
			display: flex;
			flex-direction: column;
			min-height: 100vh;
			align-items: center;
			/* Adjusted for top bar placement */
			padding-top: 0; 
			padding-bottom: 2rem;
		}

		/* --- Top Bar for Back Button (Copied from viewrecipes.php) --- */
		.top-bar {
			width: 100%;
			background-color: var(--primary-color-dark); 
			padding: 0.5rem 1.5rem;
		}
		.top-bar-content { 
			max-width: 1200px; 
			margin: 0 auto; 
			padding: 0; 
		}
		.back-link {
			color: var(--light-color);
			text-decoration: none;
			font-weight: 500;
			font-size: 0.9rem;
			transition: opacity 0.3s ease;
			display: inline-flex;
			align-items: center;
			padding: 0.5rem 0;
		}
		.back-link:hover { opacity: 0.8; }
		.back-link .fa { margin-right: 0.5rem; }
		
		h2 {
			font-family: var(--font-display);
			color: var(--primary-color-dark);
			font-size: 2rem;
			margin-top: 2rem; /* Added margin after top bar */
			margin-bottom: 1.5rem;
		}

		/* ==========================================================================
		   2. FORM CONTAINER & STYLING
		   ========================================================================== */
		form {
			width: 90%;
			max-width: 650px; 
			margin: 0 auto; /* Removed margin-top since h2 handles vertical spacing */
			padding: 3rem;
			background-color: var(--light-color);
			border: 1px solid var(--border-color);
			border-radius: 12px; 
			box-shadow: 0 8px 30px var(--shadow-color); 
		}

		label {
			display: block;
			margin-bottom: 0.5rem; 
			font-weight: 600;
			color: var(--label-color);
			font-family: var(--font-body);
		}

		input:not([type="file"]), 
		textarea,
		select {
			width: 100%;
			padding: 0.8rem 1rem; 
			margin-bottom: 1.5rem; 
			border: 1px solid var(--border-color);
			border-radius: 8px;
			background-color: var(--secondary-color); 
			font-family: var(--font-body);
			font-size: 1rem;
			transition: border-color 0.3s ease, box-shadow 0.3s ease;
		}

		/* Style for the Recipe Owner field to keep it locked */
		input[readonly] {
			background-color: #e0e0e0; 
			color: #777;
			cursor: not-allowed;
		}
		
		input:focus, 
		textarea:focus {
			outline: none;
			border-color: var(--primary-color);
			box-shadow: 0 0 0 3px rgba(131, 168, 125, 0.3); 
			background-color: var(--light-color); 
		}

		textarea {
			min-height: 150px;
			resize: vertical;
		}
		
		/* File Input Specific Styling */
		input[type="file"] {
			width: 100%;
			padding: 10px 0;
			margin-bottom: 1.5rem;
			border: none; 
		}
		
		/* Current Image Display */
		p {
			margin-bottom: 10px;
			font-size: 0.95rem;
			color: var(--text-color);
		}
		img {
			border-radius: 6px;
			border: 1px solid var(--border-color);
			margin-top: 5px;
		}

		/* Submission Button */
		button[type="submit"] {
			background-color: var(--primary-color);
			color: var(--light-color);
			padding: 1rem 2rem; 
			border: 1px solid var(--primary-color); 
			border-radius: 50px; 
			font-weight: 600;
			font-size: 1.1rem;
			cursor: pointer;
			transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
			display: block;
			width: 100%;
		}

		button[type="submit"]:hover {
			background-color: var(--primary-color-dark);
			border-color: var(--primary-color-dark);
			transform: translateY(-2px);
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
		}
		
		/* Responsive Adjustments */
		@media (max-width: 600px) {
			form {
				padding: 2rem 1.5rem;
			}
			h2 {
				font-size: 1.75rem;
			}
			button[type="submit"] {
				padding: 0.8rem 1.5rem;
				font-size: 1rem;
			}
		}
	</style>
</head>
<body>

<!-- ======================= TOP BAR (BACK BUTTON) ======================= -->
<div class="top-bar">
    <div class="top-bar-content">
        <a href="<?= htmlspecialchars($back_link_url) ?>" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Recipes
        </a>
    </div>
</div>
<!-- ======================================================================= -->

<form action="/updaterecipe" method="post" enctype="multipart/form-data">
	<h2>Edit Recipe</h2>
	
	<label for="recipename">Recipe Name:</label>
	<input type="text" id="recipename" name="recipename" value="<?= htmlspecialchars($row['recipename']) ?>">

	<label for="ingredients">Ingredients:</label>
	<textarea id="ingredients" name="ingredients"><?= htmlspecialchars($row['ingredients']) ?></textarea>

	<label for="steps">Steps:</label>
	<textarea id="steps" name="steps"><?= htmlspecialchars($row['steps']) ?></textarea>

	<label for="recipeowner">Recipe Owner:</label>
	<input type="text" id="recipeowner" name="recipeowner" value="<?= htmlspecialchars($row['recipeowner']) ?>" readonly> 

	<label for="rmedia">Media (optional):</label>
	<?php if (!empty($row['rmedia'])) : ?>
		<p>Current Image: <img src="<?= htmlspecialchars($row['rmedia']) ?>" alt="Current Image" width="100"></p>
		<input type="hidden" name="existing_image" value="<?= htmlspecialchars($row['rmedia']) ?>">
	<?php endif; ?>
	<input type="file" id="rmedia" name="rmedia">

	<input type="hidden" name="recipeid" value="<?= htmlspecialchars($recipe_id) ?>">
	<button type="submit">Update Recipe</button>
</form>

</body>
</html>

<?php
// Start the session (important if you want to use session data later)
session_start();


// Create a new MySQLi object with the defined credentials
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check for connection errors
if ($mysqli->connect_error) {
    // Fail immediately if connection fails
    die("Failed to connect to MySQL: ". $mysqli->connect_error);
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user ID and updated information from the form (using isset to avoid errors)
    $recipename = isset($_POST['recipename']) ? $_POST['recipename'] : "";
    $ingredients = isset($_POST['ingredients']) ? $_POST['ingredients'] : "";
    $steps = isset($_POST['steps']) ? $_POST['steps'] : "";
    $recipeowner = isset($_POST['recipeowner']) ? $_POST['recipeowner'] : "";
    $recipe_id = isset($_POST['recipeid']) ? $_POST['recipeid'] : 0; 

    // Check if a new file was uploaded
    if (isset($_FILES['rmedia']) && $_FILES['rmedia']['error'] === 0) {
        // Handle new file upload (validation, move to uploads directory, etc.)
        $upload_dir = 'uploads/';
        // NOTE: In a live environment, you should rename the file to prevent conflicts
        $rmedia = $upload_dir . basename($_FILES['rmedia']['name']);
        move_uploaded_file($_FILES['rmedia']['tmp_name'], $rmedia);
    } else {
        // If no new file was uploaded, use the existing image path
        $rmedia = isset($_POST['existing_image']) ? $_POST['existing_image'] : "";
    }

    // Define the SQL query with placeholders
    $query = "UPDATE recipes SET recipename = ?, ingredients = ?, steps = ?, rmedia = ?, recipeowner = ? WHERE recipeid = ?";

    // Prepare the statement
    $stmt = $mysqli->prepare($query);

    // Bind the parameters using prepared statement
    $stmt->bind_param("sssssi", $recipename, $ingredients, $steps, $rmedia, $recipeowner, $recipe_id);

    // Execute the query
    if ($stmt->execute()) {
        
        // =================================================================
        // SUCCESS MESSAGE AND REDIRECTION CODE
        // =================================================================
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Update Success</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                :root {
                    --primary-color: #83a87d; /* Soft green */
                    --primary-color-dark: #6a8d65;
                    --secondary-color: #f2eee8; /* Creamy background */
                    --heading-color: #2d2d2d;
                    --font-body: 'Poppins', sans-serif;
                    --font-display: 'Lora', serif;
                }
                body { 
                    font-family: var(--font-body); 
                    background-color: var(--secondary-color); 
                    display: flex; 
                    justify-content: center; 
                    align-items: center; 
                    height: 100vh; 
                    margin: 0; 
                }
                .success-container { 
                    text-align: center; 
                    padding: 50px; 
                    background-color: #fff; 
                    border-radius: 12px; 
                    box-shadow: 0 8px 30px rgba(0,0,0,0.1); 
                    max-width: 400px;
                }
                .success-container h2 { 
                    color: var(--primary-color-dark); 
                    margin-bottom: 15px; 
                    font-size: 24px;
                    font-family: var(--font-display);
                }
                .success-container p { 
                    color: var(--heading-color); 
                    font-size: 16px;
                }
                .loader { 
                    border: 4px solid #e0dcd5; 
                    border-top: 4px solid var(--primary-color); /* Primary Green loader */
                    border-radius: 50%; 
                    width: 40px; 
                    height: 40px; 
                    animation: spin 1s linear infinite; 
                    margin: 25px auto 0; 
                }
                @keyframes spin { 
                    0% { transform: rotate(0deg); } 
                    100% { transform: rotate(360deg); } 
                }
            </style>
        </head>
        <body>
            <div class="success-container">
                <h2>Recipe Updated Successfully!</h2>
                <p>Redirecting you to the Recipe Manager...</p>
                <div class="loader"></div>
            </div>

            <script>
                // Redirect to the recipe owner's view page after a short delay
                setTimeout(function() {
                    window.location.href = '/viewrecipes'; // Redirect to the manager page
                }, 1500); // 1.5 second delay
            </script>
        </body>
        </html>
HTML;
        
    } else {
        // If an error occurs, output a basic message
        echo "Error: " . $mysqli->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "No form data submitted";
}

// Close the MySQLi connection
$mysqli->close();
?>

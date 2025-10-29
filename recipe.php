<?php
// Start the session to check for a logged-in user.
session_start();

// --- CRITICAL CHECK: ENSURE USER IS LOGGED IN AS OWNER ---
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'recipeowner' || !isset($_SESSION['username'])) {
    // If not a logged-in owner, stop execution and show an error.
    die("Access Denied: You must be logged in as a Recipe Owner to submit a recipe.");
}

// Set the recipe owner from the secure session variable
$recipeowner_session = $_SESSION['username'];

// Configuration
$db_host= "sql206.infinityfree.com"; 
$db_username = "if0_40121371";
$db_password = "C4LaekdcxQWrOU"; 
$db_name = "if0_40121371_recipewebsite";

// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipename = $_POST["recipename"];
    $recipecategory = $_POST["recipecategory"];
    $steps = $_POST["steps"];
    $ingredients = $_POST["ingredients"];

    // Validate form data
    if (empty($recipename) || empty($ingredients) || empty($steps) || empty($recipecategory)) {
        echo "Please fill in all required fields.";
        exit;
    }

    // --- DIAGNOSTIC NOTE: This variable is now sourced from the session, not POST.
    $recipeowner = $recipeowner_session;

    // Upload media files (logic remains the same)
    $target_dir = "uploads/";
    $uploadOk = 1;
    $image_paths = []; 

    if (!empty($_FILES["rmedia"]["name"][0])) {
        foreach ($_FILES["rmedia"]["name"] as $key => $value) {
            $target_file = $target_dir . basename($value);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["rmedia"]["tmp_name"][$key]) ?? false;
            if (!$check) {
                echo "File " . htmlspecialchars(basename($value)) . " is not an image.";
                $uploadOk = 0;
                continue; 
            }
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["rmedia"]["tmp_name"][$key], $target_file)) {
                    $image_paths[] = $target_file;
                } else {
                    echo "Sorry, there was an error uploading your file " . htmlspecialchars(basename($value)) . ".";
                    $uploadOk = 0;
                }
            }
        }
    }

    $first_image_path = !empty($image_paths) ? $image_paths[0] : ""; 

    // Define SQL query with placeholders
    $sql = "INSERT INTO recipes (recipename, ingredients, rmedia, recipeowner, recipecategory, steps) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters with prepared statement
    $stmt->bind_param("ssssss", $recipename, $ingredients, $first_image_path, $recipeowner, $recipecategory, $steps);

    if ($stmt->execute()) {
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Success</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                :root {
                    --primary-color: #83a87d; --primary-color-dark: #6a8d65;
                    --secondary-color: #f2eee8; --heading-color: #2d2d2d;
                    --font-body: 'Poppins', sans-serif; --font-display: 'Lora', serif;
                }
                body { font-family: var(--font-body); background-color: var(--secondary-color); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .success-container { text-align: center; padding: 50px; background-color: #fff; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); max-width: 400px; }
                .success-container h2 { color: var(--primary-color-dark); margin-bottom: 15px; font-size: 24px; font-family: var(--font-display); }
                .success-container p { color: var(--heading-color); font-size: 16px; }
                .loader { border: 4px solid #e0dcd5; border-top: 4px solid var(--primary-color); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 25px auto 0; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        </head>
        <body>
            <div class="success-container">
                <h2>Recipe Added Successfully!</h2>
                <p>Redirecting you to the Recipe Manager...</p>
                <div class="loader"></div>
            </div>

            <script>
                // Redirect to the recipe owner's view page after a short delay
                setTimeout(function() {
                    window.location.href = '/viewrecipes'; 
                }, 1500); 
            </script>
        </body>
        </html>
HTML;
    } else {
        echo "Error adding recipe: " . htmlspecialchars($conn->error);
    }

    $stmt->close();
}

$conn->close();
?>

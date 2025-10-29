<?php
// --- This is your original, unchanged code for deleting the recipe ---
$pdo = new PDO('mysql:host=sql206.infinityfree.com;dbname=if0_40121371_recipewebsite;charset=utf8mb4', 'if0_40121371', 'C4LaekdcxQWrOU');
$recipeid = $_POST['id'];
$pdo->prepare("DELETE FROM recipes WHERE recipeid =?")->execute([$recipeid]);
// --- End of your original code ---


// =================================================================
// MODIFIED PART: DISPLAY SUCCESS MESSAGE, ADD LOADER & REDIRECT
// =================================================================
// The page to redirect to after deletion.
$redirect_page = '/viewrecipes';

echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="refresh" content="3;url=$redirect_page">
    
    <title>Deletion Successful</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            background-color: #f4f7f6; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .message-container { 
            text-align: center; 
            padding: 40px 50px; 
            background-color: #fff; 
            border-radius: 12px; 
            box-shadow: 0 6px 25px rgba(0,0,0,0.1); 
        }
        .message-container h2 { 
            color: #dc3545; /* Red color for deletion confirmation */
            margin-bottom: 15px; 
            font-size: 24px;
        }
        .message-container p { 
            color: #333; 
            font-size: 16px;
        }
        
        /* --- LOADER STYLES ADDED --- */
        .loader {
            border: 4px solid #f3f3f3; /* Light grey */
            border-top: 4px solid #dc3545; /* Red to match the title */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 25px auto 0; /* Spacing from the text above */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h2>Recipe deleted successfully!</h2>
        <p>You will be redirected back to your recipe list shortly.</p>
        
        <div class="loader"></div>
    </div>
</body>
</html>
HTML;

// It's a best practice to call exit() to ensure no further script execution.
exit;
?>
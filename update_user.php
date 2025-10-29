<?php
// --- This is your original, unchanged PHP code ---

// Create a new MySQLi object with the defined credentials
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Failed to connect to MySQL: ". $mysqli->connect_error);
}

// Get the user ID and updated information from the form
// NOTE: This part of the code is vulnerable to SQL injection.
// Using prepared statements is highly recommended for security.
$user_id = $_POST["userid"]; // Assuming you will add a hidden input for userid in your form
$username_form = $_POST["username"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$email = $_POST["email"];
// Password should be hashed, but leaving as is per your request.
// $password = $_POST["password"]; 
// $profilep = $_POST["profilep"];

// Define the SQL query to update the user's information
// Updated query to only change what's available in the form.
$query = "UPDATE registration SET fname = '$fname', lname = '$lname', email = '$email' WHERE username = '$username_form'";

// Execute the query
if ($mysqli->query($query) === TRUE) {
    // --- MODIFIED PART: DISPLAY SUCCESS MESSAGE, LOADER, AND REDIRECT ---
    // Instead of a simple echo, we output a full HTML page.
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- This meta tag will redirect to the user dashboard after a 3-second delay -->
        <meta http-equiv="refresh" content="3;url=/user">
        
        <title>Update Successful</title>
        <style>
            :root {
                --primary-color: #83a87d; /* Soft green */
                --secondary-color: #f2eee8; /* Creamy background */
                --text-color: #4a4a4a;
                --heading-color: #2d2d2d;
                --shadow-color: rgba(0, 0, 0, 0.08);
                --font-body: 'Poppins', sans-serif;
            }
            body { 
                font-family: var(--font-body), sans-serif; 
                background-color: var(--secondary-color); 
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
                box-shadow: 0 6px 25px var(--shadow-color); 
            }
            .message-container h2 { 
                color: var(--primary-color); 
                margin-bottom: 15px; 
                font-size: 24px;
            }
            .message-container p { 
                color: var(--text-color); 
                font-size: 16px;
            }
            .loader {
                border: 4px solid #f3f3f3;
                border-top: 4px solid var(--primary-color);
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
        <div class="message-container">
            <h2>User information updated successfully!</h2>
            <p>Redirecting you back to your profile...</p>
            <div class="loader"></div>
        </div>
    </body>
    </html>
HTML;

} else {
    // This is your original error message
    echo "Error: ". $mysqli->error;
}

// Close the MySQLi connection
$mysqli->close();

// We call exit() to ensure no other code runs after outputting the page.
exit;
?>

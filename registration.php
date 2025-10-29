<?php
// --- PHP FORM PROCESSING LOGIC ---

// Check if the form was submitted by checking the request method
if ($_SERVER["REQUEST_METHOD"] == "POST") {



    // Create connection
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        // Use die() for critical errors, but avoid echoing here for redirects.
        // In a real app, you would log this error.
        die("Connection failed: " . $conn->connect_error);
    }

    // --- 2. RETRIEVE AND SANITIZE FORM DATA ---
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $usertype = $_POST["usertype"];
    $profilep = $_FILES["profilep"] ?? null;

    // --- 3. VALIDATION (Server-side) ---
    // Basic validation to ensure required fields are not empty
    if (empty($fname) || empty($email) || empty($username) || empty($password) || empty($usertype)) {
        // You could set an error message here to display in the HTML below
        $error_message = "Please fill in all required fields.";
    } else {
        // --- 4. HASH PASSWORD ---
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // --- 5. HANDLE FILE UPLOAD ---
        $target_file = null; // Default to null if no file is uploaded
        if ($profilep && $profilep["error"] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            // To avoid overwriting files, create a unique filename
            $unique_name = uniqid() . '-' . basename($profilep["name"]);
            $target_file = $target_dir . $unique_name;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Basic file validation (can be more robust)
            $check = getimagesize($profilep["tmp_name"]);
            if ($check === false) { $uploadOk = 0; }
            if ($profilep["size"] > 500000) { $uploadOk = 0; }
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") { $uploadOk = 0; }

            if ($uploadOk == 1) {
                // Create uploads directory if it doesn't exist
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                move_uploaded_file($profilep["tmp_name"], $target_file);
            } else {
                $target_file = null; // Nullify if upload failed
            }
        }

        // --- 6. INSERT DATA INTO DATABASE ---
        $sql = "INSERT INTO registration (fname, lname, email, username, password, profilep, usertype) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Bind parameters: s = string, i = integer
        $stmt->bind_param("ssssssi", $fname, $lname, $email, $username, $password_hash, $target_file, $usertype);
        
        // --- 7. EXECUTE AND REDIRECT ON SUCCESS ---
        if ($stmt->execute()) {
            // SUCCESS! The user was created.
            // Now, perform the redirect to the clean URL.
            
            // IMPORTANT: Use a relative path for portability between local and live servers.
            header("Location: /loginpage"); 
            
            // ALWAYS call exit() after a header redirect to stop script execution.
            exit; 
        } else {
            // Handle database insertion error
            $error_message = "Error: Could not register the user.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Culinary Compass</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>

    <!-- Embedded Stylesheet -->
    <style>
        /* (Your existing CSS code remains unchanged here) */
        :root {
            --primary-color: #f44336;
            --primary-color-dark: #d32f2f;
            --secondary-color: #333;
            --surface-color: #ffffff;
            --text-color: #666;
            --border-color: #ddd;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --font-primary: 'Poppins', sans-serif;
            --border-radius: 8px;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: 100%; }
        body {
            font-family: var(--font-primary);
            color: var(--text-color);
            background: linear-gradient(rgba(4, 9, 30, 0.7), rgba(4, 9, 30, 0.7)), url(bimage1.jpeg) no-repeat center center/cover;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }
        .registration-card {
            background-color: var(--surface-color);
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .card-header { margin-bottom: 2rem; }
        .logo { display: block; width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; }
        .card-header h1 { font-size: 1.75rem; font-weight: 600; color: var(--secondary-color); margin-bottom: 0.25rem; }
        .card-header p { font-size: 1rem; color: var(--text-color); }
        .registration-form .form-group { margin-bottom: 1.25rem; text-align: left; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--secondary-color); }
        .form-control { width: 100%; height: 50px; padding: 0 1rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: 1rem; font-family: var(--font-primary); transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        .form-control:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.2); }
        .file-upload-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; border: 1px solid var(--border-color); border-radius: var(--border-radius); height: 50px; cursor: pointer; }
        .file-upload-wrapper input[type=file] { position: absolute; left: 0; top: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
        .file-upload-label { display: flex; align-items: center; height: 100%; padding: 0 1rem; color: var(--text-color); }
        .radio-group { border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 1rem; }
        .radio-group legend { font-weight: 500; color: var(--secondary-color); padding: 0 0.5rem; }
        .radio-option { display: flex; align-items: center; margin-bottom: 0.5rem; }
        .radio-option:last-child { margin-bottom: 0; }
        .radio-option input[type="radio"] { margin-right: 0.5rem; }
        .btn { width: 100%; height: 50px; border: none; border-radius: var(--border-radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease; font-family: var(--font-primary); margin-top: 1rem; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background-color: var(--primary-color); color: var(--surface-color); }
        .btn-primary:hover { background-color: var(--primary-color-dark); }
        .site-footer { flex-shrink: 0; width: 100%; padding: 1.5rem 0; text-align: center; color: #ddd; font-size: 0.9rem; }
        .site-footer .fa-heart-o { color: var(--primary-color); }
        @media (max-width: 576px) { .registration-card { padding: 2rem 1.5rem; } }
    </style>
</head>
<body>
    
    <main>
        <div class="registration-card">
            <header class="card-header">
                <a href="/"><img src="logo 1.jpeg" alt="Culinary Compass Logo" class="logo"></a>
                <h1>Create an Account</h1>
                <p>Join our community of food lovers!</p>
            </header>
    
            <form class="registration-form" action="/registration" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" class="form-control" placeholder="Enter First Name" name="fname" required>
                </div>

                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" class="form-control" placeholder="Enter Last Name" name="lname">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter Email" name="email">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" class="form-control" placeholder="Enter Username" name="username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Enter Password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="photo">Upload a Profile Picture</label>
                    <div class="file-upload-wrapper">
                        <span class="file-upload-label">Choose a file...</span>
                        <input type="file" name="profilep" id="photo">
                    </div>
                </div>
                
                <fieldset class="form-group radio-group">
                    <legend>Choose your role</legend>
                    <div class="radio-option">
                        <input type="radio" name="usertype" value="1" id="user" required> 
                        <label for="user">User</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="usertype" value="2" id="recipe_owner"> 
                        <label for="recipe_owner">Recipe Owner</label>
                    </div>
                </fieldset>
                
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
        </div>
    </main>
    
    <footer class="site-footer">
        <p>Made with <i class="fa fa-heart-o"></i> by Nancy Nduta Njoroge. &copy; 2025</p>
    </footer>

</body>
</html>

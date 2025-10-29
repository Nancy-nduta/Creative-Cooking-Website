<?php
// Configuration
$db_host = "sql206.infinityfree.com"; 
$db_username = "if0_40121371";
$db_password = "C4LaekdcxQWrOU"; 
$db_name = "if0_40121371_recipewebsite";

// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

// Start session
session_start();

// Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $usertype = $_POST['usertype'];

    // Check if user is an administrator
    if ($usertype == 'administrator') {
        $query = "SELECT * FROM administrators WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['usertype'] = 'administrator';
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        // Define $usertype_id based on $usertype
        if ($usertype == 'user') {
            $usertype_id = 1;
        } elseif ($usertype == 'recipeowner') {
            $usertype_id = 2;
        } else {
            $usertype_id = null; // or some default value
        }

        // Query to retrieve user data from registration table
        $query = "SELECT * FROM registration WHERE username = ? AND usertype = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $usertype_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Verify password
            if (password_verify($password, $password_hash)) {
                // Determine usertype based on usertype value
                if ($user_data['usertype'] == 1) {
                    $usertype_name = 'user';
                    $redirect_page = '/userl';
                } elseif ($user_data['usertype'] == 2) {
                    $usertype_name = 'recipeowner';
                    $redirect_page = '/recipeownerl';
                }

                // Login successful, set session variables
                $_SESSION['username'] = $username;
                $_SESSION['usertype'] = $usertype_name;

                // Redirect to user-specific page
                header('Location: ' . $redirect_page);
                exit;
            } else {
                $error = 'Invalid password';
                echo "Error: Invalid password";
            }
        } else {
            $error = 'Invalid username or usertype';
            echo "Error: Invalid username or usertype";
        }
    }
}

// Close connection
$conn->close();

// Display any error messages
if (isset($error)) {
    echo "Error: ". $error;
}
?>
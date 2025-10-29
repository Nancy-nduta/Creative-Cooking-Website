<?php

// Create a new MySQLi object with the defined credentials
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check for connection errors
if ($mysqli->connect_error) {
   ?>
    <style>
        /* Error Message Styles */
       .error-message {
            color: #f44336;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
    <div class="error-message">Failed to connect to MySQL: <?= $mysqli->connect_error?></div>
    <?php
    exit();
}

// Get the user ID from the URL parameter
$user_id = $_GET["id"];

// Define the SQL query to select the user's information
$query = "SELECT * FROM registration WHERE userid = '$user_id'";

// Execute the query and store the result
if ($result = $mysqli->query($query)) {
    $user_data = $result->fetch_assoc();
} else {
   ?>
    <style>
        /* Error Message Styles */
       .error-message {
            color: #f44336;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
    <div class="error-message">Error: <?= $mysqli->error?></div>
    <?php
}

// Close the MySQLi connection
$mysqli->close();
?>

<!-- HTML form to edit user information -->
<html>
<head>
    <title>Edit User</title>
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif ;
            background-color: #ecdcdb;
        }
        
        h1{
            margin-left: 400px;
            margin-top: 10px;
        }
        
        /* Form Styles */
        form {
            width: 400px;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }
        
        table {
            width: 100%;
        }
        
        tr {
            border-bottom: 1px solid #ddd;
        }
        
        td {
            padding: 10px;
        }
        
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            height: 40px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        input[type="submit"] {
            width: 100%;
            height: 40px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <h1>Edit User</h1>
    <form action="update_user.php" method="post">
        <input type="hidden" name="userid" value="<?= $user_id;?>">
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" value="<?= $user_data["username"];?>"></td>
            </tr>
            <tr>
                <td>First Name:</td>
                <td><input type="text" name="fname" value="<?= $user_data["fname"];?>"></td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lname" value="<?= $user_data["lname"];?>"></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input type="email" name="email" value="<?= $user_data["email"];?>"></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" value="<?= $user_data["password"];?>"></td>
            </tr>
            <tr>
                <td>Profile Picture:</td>
                <td><input type="text" name="profilep" value="<?= $user_data["profilep"];?>"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Update">
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
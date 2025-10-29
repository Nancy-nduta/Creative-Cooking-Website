<?php


// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $Usergroup = $_POST["Usergroup"];
    $profilep = $_FILES["profilep"]["name"];

    // Map role to ID
    if ($role == 'Client') {
        $role_id = 1;
    } elseif ($role == 'RecipeOwner') {
        $role_id = 2;
    }

    // Insert data into database
    $sql = "INSERT INTO registration (username, fname, lname, email, password, profilep, role_id) VALUES (?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $username, $fname, $lname, $email, $password, $profilep, $groupid);
    $stmt->execute();

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Redirect to success page
    header("Location: success.php");
    exit;
}
?>
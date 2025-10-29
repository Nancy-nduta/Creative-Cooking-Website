<?php



$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
//check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$recipeowner = $_POST['recipeowner'];
$recipecategory = $_POST['recipecategory'];

// Insert data into database
$sql = "INSERT INTO recipecategory (recipeowner, recipecategory) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $recipeowner, $recipecategory);
$stmt->execute();

// Check for errors
if ($stmt->error) {
    echo "Error: " . $stmt->error;
} else {
    echo "Recipe category added successfully!";
}

// Close database connection
$stmt->close();
$conn->close();
?>
<?php

// Start a session (optional, for user identification)
session_start();


$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get the recipe owner ID (replace with your logic to get user ID)
$recipeid = $_SESSION['recipeid']; // Assuming user ID is stored in session

// Prepare the SQL query
$sql = "SELECT * FROM recipes WHERE recipeid = ?";

$stmt = $conn->prepare($stmt);
$stmt->bind_param("i", $recipeid);
$stmt->execute();

$result = $stmt->get_result();

// Create an empty array to store recipes
$recipes = [];

// Fetch recipes and add them to the array
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $recipes[] = $row;
  }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Recipes</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <h1>My Recipes</h1>

  <div id="recipe-container">

    <?php foreach ($recipes as $recipe): ?>
      <?php displayRecipe($recipe); ?>
    <?php endforeach; ?>

  </div>

  <script src="script.js"></script>

</body>
</html>

<?php

// Function to display a single recipe (can be customized)
function displayRecipe($row) {
  echo "<div class='recipe-card'>";
  echo "<h3>" . $row['recipename'] . "</h3>";
  echo "<img src='" . $row['rmedia'] . "' alt='" . $row['title'] . "'>";
  echo "<p>" . substr($row['ingredients'], 0, 100) . "...</p>"; // Truncate description
  echo "</div>";
}

?>

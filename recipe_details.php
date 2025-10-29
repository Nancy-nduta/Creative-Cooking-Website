<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe details</title>
    <link rel="stylesheet" href="recipedetails.css">
    <style>
        body{
            font-family: cursive,Comic Sans MS;
            background-color:#e8dbd3;
        }
        .box {
            border: 1px solid #ccc;
            padding: 20px;
            width: 80%;
            margin: 40px auto;
            height: 100px;
        }
        .recipeimage {
            text-align: center;
        }
        .recipeimage img {
            width: 150%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-left: 300px;
        }
        #title {
            text-align: center;
            padding: 10px;
            margin-left: 100px;
            
        }
        .ingredients, .steps {
            padding: 20px;
        }
        .ingredients ul, .steps ol {
            padding: 0;
            list-style: numbers;
        }
        .ingredients li, .steps li {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .ingredients li:last-child, .steps li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    
<?php
session_start();


$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
  die("Connection failed: ". $conn->connect_error);
}

// Get the recipe ID from the query string
$recipeid = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Validate and sanitize the recipe ID (optional)

// Retrieve recipe details from database
$query = "SELECT * FROM recipes WHERE recipeid = $recipeid";
$result = $conn->query($query);

if ($result->num_rows == 1) {
  $row = $result->fetch_assoc();
  ?>
  
    <div id="title"> <h1><?= $row['recipename'] ?></h1></div>
    <div class="box">
    <?php if (!empty($row['rmedia'])): ?>
    <div class="recipeimage">
      <img src="<?php echo $row['rmedia']; ?>" alt="<?php echo $row['recipename']; ?>">
    </div>
    <?php endif; ?>
    <div class="ingredients">
      <h2>Ingredients:</h2>
      <ul>
        <?php $ingredients = explode(',', $row['ingredients']); ?>
        <?php foreach ($ingredients as $ingredient): ?>
        <li><?= $ingredient ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="steps">
      <h2>Steps:</h2>
      <ol>
        <?php $steps = explode(',', $row['recipecategory']); ?>
        <?php foreach ($steps as $step): ?>
        <li><?= $step ?></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
  <?php
} else {
  echo 'Recipe not found.';
}

$conn->close();
?>
</body>
</html>
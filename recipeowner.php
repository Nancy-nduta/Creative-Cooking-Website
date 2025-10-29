<?php
  // Start session
  session_start();

  // Check if user is logged in as a Recipe Owner
  if (!isset($_SESSION['usertype']) || $_SESSION['usertype']!= 'recipeowner') {
    header('Location: login.php');
    exit;
  }

  $username = $_SESSION['username'];



  $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

  if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
  }

  // Retrieve profile details from database
  $query = "SELECT * FROM registration WHERE username = '$username' AND usertype = 2";
  $result = $conn->query($query);

  if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
  } else {
    echo "Error: Unable to retrieve profile details";
    exit;
  }

  // Close connection
  $conn->close();
?>

<!-- HTML content -->
<!-- HTML content -->
<html>
  <head>
    <title>Recipe Owner Dashboard</title>
    <style>
      /* Add styles for this page */
      body {
        font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        background-color: #ecdcdb;
      }
      
      header {
        background-color: #333;
        color: #fff;
        padding: 10px;
        text-align: center;
      }
      
      nav {
        background-color: #333;
        padding: 10px;
        text-align: center;
      }
      
      nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: space-between;
      }
      
      nav li {
        margin-right: 20px;
      }
      
      nav a {
        color: #fff;
        text-decoration: none;
      }
      
      nav a:hover {
        color: #ccc;
      }
      
      h1 {
        margin-left: 400px;
        margin-top: 10px;
      }
      
      table {
        width: 80%;
        margin: 40px auto;
        border-collapse: collapse;
      }
      
      th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
      }
      
      th {
        background-color: #f0f0f0;
      }
      
     .recipeform {
        width: 80%;
        margin: 40px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }
      
     .recipeform input[type="text"],.recipeform input[type="file"],.recipeform textarea {
        width: 100%;
        height: 40px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 10px;
      }
      
     .recipeform input[type="submit"] {
        width: 100%;
        height: 40px;
        background-color: #f44336;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
      }
      
     .recipeform input[type="submit"]:hover {
        background-color: #e53935;
      }
    </style>
  </head>
  <body>
    <header>
      <nav>
        <ul>
          <li>Welcome, <?php echo $username;?>!</li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>
    </header>

    <h1>Profile Details</h1>
    <table>
      <tr>
        <th>Profile Picture</th>
        <td><img src="<?php echo $profile_data['profilep'];?>" alt="Profile Picture"></td>
      </tr>
      <tr>
        <th>First Name</th>
        <td><?php echo $profile_data['fname'];?></td>
      </tr>
      <tr>
        <th>Last Name</th>
        <td><?php echo $profile_data['lname'];?></td>
      </tr>
      <tr>
        <th>Email</th>
        <td><?php echo $profile_data['email'];?></td>
      </tr>
      <tr>
        <th>Username</th>
        <td><?php echo $profile_data['username'];?></td>
      </tr>
     
    </table>

    <h1>Add Recipe</h1>
    <div class="recipeform">
      <form action="http://localhost/RECIPEWEBSITE/recipeproject/recipe.php" method="post" enctype="multipart/form-data">
        <!-- Field for the name of the recipe -->
        <input type="text" required name="recipename" id="recipename" placeholder="Enter Recipe Name"><br>
        
        <!-- Field for inserting images for the recipe -->
        <label for="rmedia">Relevant media:</label>
        <input type="file" multiple name="rmedia[]" id="rmedia"><br>
        
        <!-- Field for typing out the ingredients -->
        <input type="text" maxlength="1000" name="ingredients" id="ingredients" placeholder="Enter Ingredients"><br>
        
        <!-- Field for the procedure -->
        <input type="text" maxlength="1500" name="steps" required id="steps" placeholder="Enter the procedure"><br>
        
                   <!-- Field for the name of the owner of the recipe -->
        <input type="text" placeholder="Enter your First and Last name" name="recipeowner" id="recipeowner"><br>
        
        <!-- Field for the recipe category -->
        <input type="text" name="recipecategory" id="recipecategory" placeholder="Choose Recipe Category"><br>
        
        <!-- Submission button -->
        <input type="submit"><br>
      </form>
    </div>
  </body>
</html>
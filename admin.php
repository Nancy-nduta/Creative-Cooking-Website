<?php
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype']!= 'administrator') {
  header('Location: login.php');
  exit;
}

// Connect to database
$hostname = "sql206.infinityfree.com"; 
$username = "if0_40121371";
$password = "C4LaekdcxQWrOU"; 
$database = "if0_40121371_recipewebsite";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
  die("Connection failed: ". $conn->connect_error);
}

// Retrieve all user data
$query = "SELECT * FROM registration";
$result = $conn->query($query);

if ($result->num_rows > 0) {
  ?>
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
    
    nav img{
      width: 80px;
      border-radius: 10px;
    }
    
    h1{
      margin-left: 400px;
      margin-top: 10px;
    }
    
    /* Table Styles */
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px auto;
      border: 1px solid #ddd; /* Add border to table */
    }
    
    th, td {
      padding: 10px;
      text-align: left;
      border: 1px solid #ddd; /* Add border to cells */
    }
    
    th {
      background-color: #333; /* Orange header */
      color: #fff;
    }
    
    tr {
      background-color: #fff; /* White rows */
    }
    
    .edit-btn {
      background-color: #f44336;
      color: #fff;
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    
    .edit-btn:hover {
      background-color: #3e8e41;
    }
  </style>
  <table>
    <tr>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Email</th>
      <th>Password</th>
      <th>Profile Picture</th>
      <th>User ID</th>
      <th>Username</th>
      <th>User Type</th>
      <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['fname'] ?></td>
        <td><?= $row['lname'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['password'] ?></td>
        <td><?= $row['profilep'] ?></td>
        <td><?= $row['userid'] ?></td>
        <td><?= $row['username'] ?></td>
        <td><?= $row['usertype'] ?></td>
        <td>
          <button class="edit-btn" onclick="location.href='edit_user.php?id=<?= $row['userid'] ?>'">Edit</button>
          <button class="edit-btn" onclick="location.href='delete_user.php?id=<?= $row['userid'] ?>'">Delete</button>
        </td>
      </tr>
    <?php } ?>

  </table>
  <?php
} else {
  echo 'No users found';
}

$conn->close();
?>
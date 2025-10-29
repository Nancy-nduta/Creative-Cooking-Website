<?php
// Connect to the database

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
  die("Connection failed: ". $conn->connect_error);
}

// Get the user ID from the URL parameter
$user_id = $_GET['id'];

// Check if the user ID is valid
if (empty($user_id) ||!is_numeric($user_id)) {
 ?>
  <style>
    /* Error Message Styles */
   .error-message {
      color: #f44336;
      font-weight: bold;
      margin-bottom: 20px;
    }
  </style>
  <div class="error-message">Invalid user ID</div>
  <?php
  exit;
}

// Delete the user from the database
$query = "DELETE FROM registration WHERE userid = '$user_id'";
if ($conn->query($query) === TRUE) {
 ?>
  <style>
    /* Success Message Styles */
   .success-message {
      color: #4CAF50;
      font-weight: bold;
      margin-bottom: 20px;
    }
  </style>
  <div class="success-message">User deleted successfully</div>
  <?php
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
  <div class="error-message">Error deleting user: <?= $conn->error?></div>
  <?php
}

$conn->close();
?>
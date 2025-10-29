<?php

// Create a new MySQLi object with the defined credentials
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check for connection errors
if ($mysqli->connect_error) {
    echo "Failed to connect to MySQL: ". $mysqli->connect_error;
    exit();
}

// Define the SQL query to select all records from the "registration" table
$query = "SELECT * FROM registration";

// Execute the query and store the result
if ($result = $mysqli->query($query)) {
    // Start the HTML document
   ?>
    <html>
    <head>
        <title> User Credentials</title>
        <style>
            /* Define the table styles */
            table {
                border-collapse: collapse;
                width: 100%;
                font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif
                ;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <h1>Registration Table</h1>
        <table>
            <tr>
                <!-- Define the table headers -->
                <th>User ID</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Profile Picture</th>
                <th>Edit</th>
            </tr>
            <?php
            // Loop through each row in the result set
            while ($row = $result->fetch_assoc()) {
               ?>
                <tr>
                    <!-- Display each column value in a table cell -->
                    <td><?= $row["userid"];?></td>
                    <td><?= $row["username"];?></td>
                    <td><?= $row["fname"];?></td>
                    <td><?= $row["lname"];?></td>
                    <td><?= $row["email"];?></td>
                    <td><?= $row["password"];?></td>
                    <td><?= $row["profilep"];?></td>
                    <td>
                        <!-- Add an "Edit" button with a link to edit_user.php -->
                        <a href="edit_user.php?id=<?= $row["userid"];?>">Edit</a>
                    </td>
                </tr>
                <?php
            }
           ?>
        </table>
    </body>
    </html>
    <?php
    // Free the result set
    $result->free();
} else {
    // If there's an error, display the error message
    echo "Error: ". $mysqli->error;
}

// Close the MySQLi connection
$mysqli->close();
?>
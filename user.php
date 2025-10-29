<?php
 // Start session
 session_start();

 // Check if user is logged in
 if (!isset($_SESSION['username'])) {
   header('Location: loginpage.php'); // Corrected link
   exit;
 }

 $username = $_SESSION['username'];
 $usertype = $_SESSION['usertype'];

 

 $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

 if ($conn->connect_error) {
   die("Connection failed: ". $conn->connect_error);
 }

 // Retrieve user details from database using a prepared statement for security
 $query = "SELECT fname, lname, email, username FROM registration WHERE username = ?";
 $stmt = $conn->prepare($query);
 $stmt->bind_param("s", $username);
 $stmt->execute();
 $result = $stmt->get_result();
 

 if ($result->num_rows > 0) {
   $user_data = $result->fetch_assoc();
 } else {
   // A more graceful error handling
   $user_data = ['fname' => 'N/A', 'lname' => 'N/A', 'email' => 'N/A', 'username' => $username];
 }

 // Close connection
 $stmt->close();
 $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Culinary Compass</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>

    <!-- Embedded Stylesheet -->
    <style>
        :root {
            --primary-color: #83a87d; /* Soft green */
            --primary-color-dark: #6a8d65;
            --secondary-color: #f2eee8; /* Creamy background */
            --text-color: #4a4a4a;
            --heading-color: #2d2d2d;
            --border-color: #e0dcd5;
            --light-color: #ffffff;
            --danger-color: #dc3545;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --font-body: 'Poppins', sans-serif;
            --font-display: 'Lora', serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.7;
        }
        
        /* --- Header & Navigation --- */
        .site-header {
            background-color: var(--primary-color-dark);
            color: var(--light-color);
            padding: 1rem 1.5rem;
        }
        .site-header nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-left a, .nav-right-item {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .logout-btn {
            background-color: transparent;
            color: var(--light-color);
            border: 1px solid var(--light-color);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: var(--light-color);
            color: var(--primary-color-dark);
        }

        /* --- Main Content & Profile Card --- */
        main {
            padding: 4rem 1.5rem;
        }
        .user-info {
            width: 100%;
            max-width: 600px;
            background-color: var(--light-color);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px var(--shadow-color);
            margin: 0 auto;
        }
        .user-info h2 {
            font-family: var(--font-display);
            font-size: 2.25rem;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--heading-color);
        }
        
        /* --- Profile Details Styling --- */
        .profile-detail {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .profile-detail:last-child {
            border-bottom: none;
        }
        .profile-detail-label {
            font-weight: 600;
            color: var(--heading-color);
        }
        .profile-detail-value {
            color: var(--text-color);
        }
        
        /* --- Edit Form Styling --- */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--heading-color); }
        .form-control {
            width: 100%; height: 50px; padding: 0 1rem;
            border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem;
            font-family: var(--font-body); background-color: var(--secondary-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus {
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(131, 168, 125, 0.25);
            background-color: var(--light-color);
        }
        .form-control[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .form-actions { display: flex; gap: 1rem; margin-top: 2rem; }
        .btn {
            flex-grow: 1; padding: 0.8rem; border: none; border-radius: 8px;
            font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-primary { background-color: var(--primary-color); color: var(--light-color); }
        .btn-primary:hover { background-color: var(--primary-color-dark); }
        .btn-secondary { background-color: transparent; border: 1px solid var(--border-color); color: var(--text-color); }
        .btn-secondary:hover { background-color: var(--secondary-color); }
        
        .profile-actions { margin-top: 2rem; text-align: right; }
        .edit-profile-btn {
            background-color: var(--primary-color); color: var(--light-color);
            padding: 0.6rem 1.2rem; border-radius: 50px; border: none; cursor: pointer;
            font-weight: 500; font-size: 0.9rem; transition: background-color 0.3s ease;
        }
        .edit-profile-btn:hover { background-color: var(--primary-color-dark); }
        .hidden { display: none; }
    </style>
</head>
<body>
    <header class="site-header">
      <nav>
        <div class="nav-left">
          <a href="/userl">
            <i class="fa fa-home"></i> Home
          </a>
        </div>
        <div class="nav-right">
          <span class="nav-right-item">Welcome, <?php echo htmlspecialchars($username);?>!</span>
          <a href="/logout" class="logout-btn">Logout</a>
        </div>
      </nav>
    </header>
    
    <main>
        <div class="user-info">
          <h2>Profile Details</h2>

          <!-- =========== DISPLAY VIEW (Visible by default) =========== -->
          <div id="profile-display">
              <div class="profile-detail">
                  <span class="profile-detail-label">First Name</span>
                  <span class="profile-detail-value"><?php echo htmlspecialchars($user_data['fname']);?></span>
              </div>
              <div class="profile-detail">
                  <span class="profile-detail-label">Last Name</span>
                  <span class="profile-detail-value"><?php echo htmlspecialchars($user_data['lname']);?></span>
              </div>
              <div class="profile-detail">
                  <span class="profile-detail-label">Email</span>
                  <span class="profile-detail-value"><?php echo htmlspecialchars($user_data['email']);?></span>
              </div>
              <div class="profile-detail">
                  <span class="profile-detail-label">Username</span>
                  <span class="profile-detail-value"><?php echo htmlspecialchars($user_data['username']);?></span>
              </div>
              <div class="profile-actions">
                  <button id="edit-btn" class="edit-profile-btn"><i class="fa fa-pencil"></i> Edit Profile</button>
              </div>
          </div>
          
          <!-- =========== EDIT FORM (Hidden by default) =========== -->
          <form id="edit-profile-form" action="/update_user" method="POST" class="hidden">
              <div class="form-group">
                  <label for="fname">First Name</label>
                  <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($user_data['fname']); ?>" required>
              </div>
              <div class="form-group">
                  <label for="lname">Last Name</label>
                  <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($user_data['lname']); ?>" required>
              </div>
              <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
              </div>
              <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
              </div>
              <div class="form-actions">
                  <button type="button" id="cancel-btn" class="btn btn-secondary">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
              </div>
          </form>

        </div>
    </main>

    <script>
        // --- JavaScript to toggle between display and edit modes ---
        const displayView = document.getElementById('profile-display');
        const editForm = document.getElementById('edit-profile-form');
        const editButton = document.getElementById('edit-btn');
        const cancelButton = document.getElementById('cancel-btn');

        editButton.addEventListener('click', () => {
            displayView.classList.add('hidden');
            editForm.classList.remove('hidden');
        });

        cancelButton.addEventListener('click', () => {
            editForm.classList.add('hidden');
            displayView.classList.remove('hidden');
        });
    </script>
</body>
</html>


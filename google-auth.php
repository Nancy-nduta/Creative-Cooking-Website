<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>



<?php
// Start the session to manage user login state.
session_start();

// Include Google API client (install via Composer)
require_once 'vendor/autoload.php'; 


$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- GOOGLE API CONFIGURATION ---
$clientID = 'YOUR_CLIENT_ID_HERE';
$clientSecret = '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';
$redirectUri = 'YOUR_CLIENT_SECRET_HERE'; // Must match Google Console

// Create a new Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// --- OAUTH 2.0 FLOW ---
if (isset($_GET['code'])) {
    // Step 2: Handle the callback from Google
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        header('Location: /login?error=google_auth_failed');
        exit();
    }
    
    $client->setAccessToken($token['access_token']);

    // Get user profile info
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $email = $google_account_info->email;
    $name = $google_account_info->name;

    // --- USER LOGIN OR REGISTRATION ---
    $stmt = $conn->prepare("SELECT * FROM registration WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Existing user
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['usertype'] = ($user['usertype'] == 2) ? 'recipeowner' : 'user';
    } else {
        // New user registration
        $username = strtok($email, '@');
        $base_username = $username;
        $counter = 1;

        while (true) {
            $check_user_stmt = $conn->prepare("SELECT userid FROM registration WHERE username = ?");
            $check_user_stmt->bind_param("s", $username);
            $check_user_stmt->execute();
            if ($check_user_stmt->get_result()->num_rows == 0) break;
            $username = $base_username . $counter++;
            $check_user_stmt->close();
        }

        $usertype = 1; // Default to normal user
        $name_parts = explode(" ", $name);
        $fname = $name_parts[0];
        $lname = isset($name_parts[1]) ? $name_parts[1] : '';

        // Random password (since Google handles auth)
        $hashed_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $insert_stmt = $conn->prepare("INSERT INTO registration (username, fname, lname, email, password, usertype) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssssi", $username, $fname, $lname, $email, $hashed_password, $usertype);
        $insert_stmt->execute();
        $insert_stmt->close();

        $_SESSION['username'] = $username;
        $_SESSION['usertype'] = 'user';
    }

    // --- CLEANUP ---
    $stmt->close();

    // --- REDIRECT BASED ON USER TYPE ---
    $redirectUrl = '/index'; // Default redirect

    if (isset($_SESSION['usertype'])) {
        switch ($_SESSION['usertype']) {
            case 'recipeowner':
                $redirectUrl = '/recipeownerl'; // Recipe owner dashboard
                break;
            case 'user':
            default:
                $redirectUrl = '/userl'; // Regular user dashboard
                break;
        }
    }

    // Redirect and end execution
    header('Location: ' . $redirectUrl);
    exit();

} else {
    // Step 1: Redirect user to Google's OAuth consent screen
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
}
?>

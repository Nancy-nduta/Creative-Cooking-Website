<?php
// =================================================================
// 1. START THE SESSION
// =================================================================
// Always start the session to access and manage session data.
// It's safe to call this even if a session is already active.
session_start();


// =================================================================
// 2. CLEAR ALL SESSION VARIABLES
// =================================================================
// Overwrite the $_SESSION superglobal with an empty array.
// This is a secure and universal way to clear all session data,
// regardless of whether it's a normal user or a recipe owner.
$_SESSION = array();


// =================================================================
// 3. DESTROY THE SESSION
// =================================================================
// This function destroys all of the data associated with the current
// session, removing the session file from the server.
session_destroy();


// =================================================================
// 4. PREVENT BROWSER CACHING (SECURITY MEASURE)
// =================================================================
// These headers instruct the browser not to cache pages. This is
// critical for preventing a logged-out user from using the "back"
// button to view a previously cached dashboard or profile page.
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.


// =================================================================
// 5. REDIRECT TO THE HOME PAGE
// =================================================================
// After the session is completely terminated, redirect the user
// to your main public landing page.
header('Location: /index');


// =================================================================
// 6. EXIT SCRIPT EXECUTION
// =================================================================
// It is a best practice to call exit() after a header redirect
// to ensure no further code is executed.
exit;
?>


<?php
// FILE: logout.php
// Admin (Instructor) logout page.

require_once 'db_connect.php'; // Starts the session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// JavaScript Redirection to the admin login page
redirect('admin_login.php');

// Since redirect() calls exit(), the rest of the script is not executed.
?>
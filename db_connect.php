<?php
// FILE: db_connect.php
// Central file for database connection and session management.

// Start session for instructor/admin tracking
session_start();

// Database Configuration
// NOTE: Assuming default credentials for a local XAMPP/WAMP environment.
// CHANGE THESE VALUES FOR PRODUCTION
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Leave blank if you have no password set in phpMyAdmin
$db_name = 'E_Learning';

// Attempt to connect to MySQL database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    // Log the error internally but show a generic message to the user
    error_log("Connection failed: " . $conn->connect_error);
    die("<h1>Database Connection Error</h1><p>We are currently experiencing technical difficulties. Please try again later.</p>");
}

/**
 * Function to sanitize and validate input data.
 * @param string $data The input string to be sanitized.
 * @return string The sanitized string.
 */
function validate_input($data) {
    global $conn;
    $data = trim($data); // Strip whitespace
    $data = stripslashes($data); // Remove backslashes
    $data = htmlspecialchars($data); // Convert special characters to HTML entities
    $data = $conn->real_escape_string($data); // Escape characters for SQL
    return $data;
}

/**
 * Checks if the user is an authenticated administrator.
 * Used for appropriate Session Tracking.
 */
function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Redirects the user to a specified page.
 * Implements JavaScript Redirection.
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    echo "<script>window.location.href = '{$url}';</script>";
    exit();
}

/**
 * Simple HTML header structure for consistency.
 * Includes the external CSS and the Dark Mode script.
 * @param string $title The title of the page.
 */
function get_page_header($title) {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title | E-Learning Portal</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="js/script.js" defer></script>
</head>
<body class="light-mode">
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"><i class="fas fa-graduation-cap"></i> E-Learning Hub</a>
            <button id="dark-mode-toggle" class="icon-button" title="Toggle Light/Dark Mode"><i class="fas fa-moon"></i></button>
        </div>
    </header>
    <main class="container">
HTML;
}

/**
 * Simple HTML footer structure for consistency.
 */
function get_page_footer() {
    return <<<HTML
    </main>
    <footer>
        <div class="container">
            <p>&copy; 2025 E-Learning Hub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
HTML;
}
?>
<?php
// FILE: admin_login.php
// Instructor/Admin login page.

require_once 'db_connect.php';

// Check if admin is already logged in, redirect to dashboard
if (is_admin()) {
    redirect('admin_dashboard.php');
}

$login_error = '';

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PHP/Server-side validation
    $username = validate_input($_POST['username']);
    $password = validate_input($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        $login_error = '<div class="alert-error">Username and password are required.</div>';
    } else {
        // Query database to find admin by username
        // IMPORTANT: The system only allows admins already existing in the database to log in.
        $stmt = $conn->prepare("SELECT admin_id, password, full_name FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        // Check if admin exists and password matches (simple string compare as requested)
        // NOTE: In a real system, you must use password_verify().
        if ($admin && $admin['password'] === $password) {
            // Login successful: Implement Appropriate Session Tracking
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            
            // Redirect to admin dashboard
            redirect('admin_dashboard.php');
        } else {
            $login_error = '<div class="alert-error"><i class="fas fa-times-circle"></i> Invalid username or password. Only existing administrators can log in.</div>';
        }
    }
}

echo get_page_header("Instructor Login");
?>

<div class="card card-center">
    <a name="login-anchor"></a> <!-- Named Anchor -->
    <h1><i class="fas fa-sign-in-alt"></i> Instructor Login</h1>
    <p class="my-4">Enter your credentials to access the administration panel.</p>

    <?php echo $login_error; ?>

    <form method="POST" action="admin_login.php">
        <!-- Proper use of Layout Tables for form elements -->
        <table class="form-table">
            <tr>
                <td><label for="username">Username:</label></td>
                <td>
                    <input type="text" id="username" name="username" required placeholder="Username">
                    <span id="username-error" class="error-message"></span>
                </td>
            </tr>
            <tr>
                <td><label for="password">Password:</label></td>
                <td>
                    <input type="password" id="password" name="password" required placeholder="Password">
                    <span id="password-error" class="error-message"></span>
                </td>
            </tr>
        </table>

        <div class="form-actions">
            <button type="submit" class="button button-primary">
                <i class="fas fa-lock-open"></i> Login
            </button>
        </div>
    </form>
    
    <p class="text-center my-4"><a href="index.php" class="link-button button-secondary">Go to Home</a></p>
</div>

<?php
echo get_page_footer();
$conn->close();
?>
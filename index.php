<?php
// FILE: index.php
// Home page to choose between Admin login or User access.

require_once 'db_connect.php'; // Includes session start and header/footer functions

echo get_page_header("Welcome to E-Learning Hub");
?>

<div class="card card-center">
    <a name="top"></a> <!-- Named Anchor -->
    <h1><i class="fas fa-home"></i> Welcome to the E-Learning Hub</h1>
    <p class="my-4">Please choose your role to proceed:</p>

    <div class="flex-group">
        <a href="user_dashboard.php" class="button button-primary link-button">
            <i class="fas fa-user-graduate"></i> Student Access
        </a>
        <a href="admin_login.php" class="button button-secondary link-button">
            <i class="fas fa-chalkboard-teacher"></i> Instructor Login
        </a>
    </div>
</div>

<?php
echo get_page_footer();
?>
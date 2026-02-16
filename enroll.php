<?php
// FILE: enroll.php
// Student enrollment form for a specific class.

require_once 'db_connect.php';

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$message = '';
$class_details = null;

// 1. Fetch class details for display
if ($class_id > 0) {
    $stmt = $conn->prepare("SELECT c.class_name, co.course_name FROM classes c JOIN courses co ON c.course_id = co.course_id WHERE c.class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $class_details = $result->fetch_assoc();
    $stmt->close();
}

// 2. Process Enrollment Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $class_id > 0) {
    // PHP/Server-side validation of user inputs
    $full_name = validate_input($_POST['full_name']);
    $email = validate_input($_POST['email']);
    $phone = validate_input($_POST['phone']);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone)) {
        $message = '<div class="alert-error">All fields are required.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert-error">Invalid email format.</div>';
    } else {
        $conn->begin_transaction();
        try {
            // A. Check if the user already exists (based on email)
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $user_id = 0;

            if ($user) {
                // User exists, use existing ID
                $user_id = $user['user_id'];
            } else {
                // User does not exist, insert new user
                $stmt_insert_user = $conn->prepare("INSERT INTO users (full_name, email, phone) VALUES (?, ?, ?)");
                $stmt_insert_user->bind_param("sss", $full_name, $email, $phone);
                $stmt_insert_user->execute();
                $user_id = $conn->insert_id;
                $stmt_insert_user->close();
            }
            
            // B. Attempt to enroll the user in the class
            $stmt_enroll = $conn->prepare("INSERT INTO enrollments (user_id, class_id) VALUES (?, ?)");
            $stmt_enroll->bind_param("ii", $user_id, $class_id);
            $stmt_enroll->execute();
            
            $conn->commit();
            $message = '<div class="alert-success"><i class="fas fa-check-circle"></i> Enrollment successful! You are now enrolled in the class: ' . htmlspecialchars($class_details['class_name']) . ' from ' . htmlspecialchars($class_details['course_name']) . '.</div>';
            // Clear POST data after success
            $_POST = []; 

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            // Check for duplicate enrollment error (MySQL error code 1062 for unique key violation)
            if ($e->getCode() == 1062) {
                 $message = '<div class="alert-error"><i class="fas fa-exclamation-triangle"></i> You are already enrolled in this class.</div>';
            } else {
                error_log("Enrollment error: " . $e->getMessage());
                $message = '<div class="alert-error">An error occurred during enrollment. Please try again.</div>';
            }
        }
    }
}

echo get_page_header("Enroll in Class");
?>

<a name="top"></a> <!-- Named Anchor -->
<h1 class="text-center"><i class="fas fa-clipboard-list"></i> Class Enrollment</h1>

<?php if (!$class_details): ?>
    <div class="card card-center">
        <p class="text-center">Error: Class not found.</p>
        <p class="text-center mt-4"><a href="user_dashboard.php" class="link-button button-secondary">Go to Course Catalog</a></p>
    </div>
<?php else: ?>

    <div class="card card-center">
        <h2 style="color: var(--clr-text); margin-bottom: 10px;">Enrollment for:</h2>
        <p style="font-size: 1.2rem; font-weight: 700; color: var(--clr-primary);"><?php echo htmlspecialchars($class_details['course_name']); ?></p>
        <p style="font-size: 1.1rem; color: var(--clr-accent);">Class: <?php echo htmlspecialchars($class_details['class_name']); ?></p>

        <?php echo $message; ?>

        <form method="POST" action="enroll.php?class_id=<?php echo $class_id; ?>">
            <!-- Proper use of Layout Tables for form elements -->
            <table class="form-table">
                <tr>
                    <td><label for="full_name">Full Name:</label></td>
                    <td>
                        <input type="text" id="full_name" name="full_name" required placeholder="Your Full Name">
                        <span id="full_name-error" class="error-message"></span>
                    </td>
                </tr>
                <tr>
                    <td><label for="email">Email:</label></td>
                    <td>
                        <input type="email" id="email" name="email" required placeholder="Your Email Address">
                        <span id="email-error" class="error-message"></span>
                    </td>
                </tr>
                <tr>
                    <td><label for="phone">Phone Number:</label></td>
                    <td>
                        <input type="text" id="phone" name="phone" required placeholder="Your Phone Number">
                        <span id="phone-error" class="error-message"></span>
                    </td>
                </tr>
            </table>

            <div class="form-actions">
                <button type="submit" class="button button-primary">
                    <i class="fas fa-calendar-check"></i> Complete Enrollment
                </button>
                <a href="user_dashboard.php" class="link-button button-secondary">Cancel</a>
            </div>
        </form>
    </div>

<?php endif; ?>

<?php
echo get_page_footer();
$conn->close();
?>
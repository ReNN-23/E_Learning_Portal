<?php
// FILE: contact.php
// Page for users to send questions to the admin/support.

require_once 'db_connect.php';

$message = '';

// Process Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PHP/Server-side validation
    $sender_name = validate_input($_POST['sender_name']);
    $sender_email = validate_input($_POST['sender_email']);
    $subject = validate_input($_POST['subject']);
    $user_message = validate_input($_POST['message']); // Renamed to avoid PHP keyword conflict

    // Basic validation
    if (empty($sender_name) || empty($sender_email) || empty($subject) || empty($user_message)) {
        $message = '<div class="alert-error">All fields are required.</div>';
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert-error">Invalid email format.</div>';
    } else {
        // Insert into contacts table
        $stmt = $conn->prepare("INSERT INTO contacts (sender_name, sender_email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $sender_name, $sender_email, $subject, $user_message);

        if ($stmt->execute()) {
            $message = '<div class="alert-success"><i class="fas fa-envelope"></i> Thank you! Your message has been sent successfully. We will get back to you soon.</div>';
            // Clear POST data after success
            $_POST = [];
        } else {
            error_log("Contact form submission error: " . $stmt->error);
            $message = '<div class="alert-error">Sorry, an error occurred while sending your message.</div>';
        }
        $stmt->close();
    }
}

echo get_page_header("Contact Us / Help");
?>

<a name="top"></a> <!-- Named Anchor -->
<h1 class="text-center"><i class="fas fa-headset"></i> Contact Us</h1>
<p class="text-center my-4">Have a question? Fill out the form below and we'll be in touch.</p>

<div class="card card-center">
    <?php echo $message; ?>

    <form method="POST" action="contact.php">
        <!-- Proper use of Layout Tables for form elements -->
        <table class="form-table">
            <tr>
                <td><label for="sender_name">Your Name:</label></td>
                <td>
                    <input type="text" id="sender_name" name="sender_name" required placeholder="Full Name" value="<?php echo htmlspecialchars($_POST['sender_name'] ?? ''); ?>">
                    <span id="sender_name-error" class="error-message"></span>
                </td>
            </tr>
            <tr>
                <td><label for="sender_email">Your Email:</label></td>
                <td>
                    <input type="email" id="sender_email" name="sender_email" required placeholder="Email Address" value="<?php echo htmlspecialchars($_POST['sender_email'] ?? ''); ?>">
                    <span id="sender_email-error" class="error-message"></span>
                </td>
            </tr>
            <tr>
                <td><label for="subject">Subject:</label></td>
                <td>
                    <input type="text" id="subject" name="subject" required placeholder="Topic of your inquiry" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    <span id="subject-error" class="error-message"></span>
                </td>
            </tr>
            <tr>
                <td><label for="message">Message:</label></td>
                <td>
                    <textarea id="message" name="message" required placeholder="Your question or feedback..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    <span id="message-error" class="error-message"></span>
                </td>
            </tr>
        </table>

        <div class="form-actions">
            <button type="submit" class="button button-primary">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </div>
    </form>
</div>

<?php
echo get_page_footer();
$conn->close();
?>
<?php
// FILE: user_view_videos.php
// Page where enrolled users can view videos for a specific course.

require_once 'db_connect.php';

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$message = '';

// Check for user enrollment via POST data (simulating a simple auth check)
$is_enrolled = false;
$user_email = '';
$course_name = '';

// 1. Fetch course details
if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $course_name = $course_details['course_name'] ?? 'Course';
}

// 2. Process Enrollment Check Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $course_id > 0) {
    $user_email = validate_input($_POST['user_email']);
    
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert-error">Please enter a valid email address.</div>';
    } else {
        // Check if the user is enrolled in ANY class of this course
        $sql = "SELECT e.enrollment_id 
                FROM enrollments e
                JOIN classes c ON e.class_id = c.class_id
                JOIN users u ON e.user_id = u.user_id
                WHERE c.course_id = ? AND u.email = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $course_id, $user_email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $is_enrolled = true;
            // Store email in session to avoid re-entering, for this page only (not a proper login)
            $_SESSION['view_video_email'] = $user_email; 
            $_SESSION['view_video_course_id'] = $course_id;
        } else {
            $message = '<div class="alert-error"><i class="fas fa-lock"></i> You must be enrolled in a class for ' . htmlspecialchars($course_name) . ' to view videos.</div>';
        }
        $stmt->close();
    }
} 
// Check session for pre-authenticated view
elseif (isset($_SESSION['view_video_email']) && $_SESSION['view_video_course_id'] == $course_id) {
    $is_enrolled = true;
    $user_email = $_SESSION['view_video_email'];
}


echo get_page_header("View Course Videos");
?>

<a name="top"></a> <!-- Named Anchor -->
<h1 class="text-center"><i class="fas fa-tv"></i> Course Videos: <?php echo htmlspecialchars($course_name); ?></h1>

<?php if ($course_id <= 0 || !$course_details): ?>
    <div class="card card-center">
        <p class="text-center">Error: Course not found.</p>
        <p class="text-center mt-4"><a href="user_dashboard.php" class="link-button button-secondary">Go to Course Catalog</a></p>
    </div>
<?php elseif (!$is_enrolled): ?>
    <div class="card card-center">
        <h2><i class="fas fa-user-lock"></i> Enrollment Check</h2>
        <p class="my-4">Please enter the email you used for enrollment to verify access.</p>
        <?php echo $message; ?>
        <form method="POST" action="user_view_videos.php?course_id=<?php echo $course_id; ?>">
            <table class="form-table">
                <tr>
                    <td><label for="user_email">Enrollment Email:</label></td>
                    <td>
                        <input type="email" id="user_email" name="user_email" required placeholder="Your Enrollment Email" value="<?php echo htmlspecialchars($user_email); ?>">
                        <span id="user_email-error" class="error-message"></span>
                    </td>
                </tr>
            </table>
            <div class="form-actions">
                <button type="submit" class="button button-primary"><i class="fas fa-sign-in-alt"></i> Verify Access</button>
            </div>
        </form>
    </div>
<?php else: 
    // User is enrolled, display videos
    $videos_sql = "SELECT video_title, video_url FROM course_videos WHERE course_id = ?";
    $stmt_videos = $conn->prepare($videos_sql);
    $stmt_videos->bind_param("i", $course_id);
    $stmt_videos->execute();
    $videos_result = $stmt_videos->get_result();
    $stmt_videos->close();
?>
    <p class="text-center my-4" style="color: green;"><i class="fas fa-user-check"></i> Access Granted for: <strong><?php echo htmlspecialchars($user_email); ?></strong></p>

    <?php if ($videos_result->num_rows > 0): ?>
        <div class="video-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php while($video = $videos_result->fetch_assoc()): ?>
                <div class="card">
                    <h3 style="color: var(--clr-primary); margin-bottom: 15px;"><i class="fas fa-play-circle"></i> <?php echo htmlspecialchars($video['video_title']); ?></h3>
                    <!-- Display video embed or link - assuming a simple link or embed URL -->
                    <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background: #000;">
                        <!-- Using iframe for embed, if the URL is a direct embed link -->
                        <iframe 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                            src="<?php echo htmlspecialchars($video['video_url']); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card text-center">
            <p>No videos have been uploaded for this course yet.</p>
        </div>
    <?php endif; ?>

<?php endif; ?>

<a href="#top" class="back-to-top">Back to Top</a>

<?php
echo get_page_footer();
$conn->close();
?>
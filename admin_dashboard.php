<?php
// FILE: admin_dashboard.php
// Main Admin (Instructor) dashboard to manage courses and view enrollments.

require_once 'db_connect.php';

// Authorization check
if (!is_admin()) {
    redirect('admin_login.php');
}

echo get_page_header("Admin Dashboard");

$message = '';

// Handle Course Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_name = validate_input($_POST['course_name']);
    $course_desc = validate_input($_POST['course_description']);

    if (empty($course_name) || empty($course_desc)) {
        $message = '<div class="alert-error">Course Name and Description are required.</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $course_name, $course_desc);
        if ($stmt->execute()) {
            $message = '<div class="alert-success"><i class="fas fa-check"></i> New Course added successfully!</div>';
        } else {
            $message = '<div class="alert-error">Error adding course: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Fetch all courses and associated classes for display
$sql = "SELECT c.course_id, c.course_name, c.course_description, 
               cls.class_id, cls.class_name, cls.class_date, cls.class_time 
        FROM courses c 
        LEFT JOIN classes cls ON c.course_id = cls.course_id 
        ORDER BY c.course_name, cls.class_date";
$result = $conn->query($sql);
$courses_data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $course_id = $row['course_id'];
        if (!isset($courses_data[$course_id])) {
            $courses_data[$course_id] = ['name' => $row['course_name'], 'desc' => $row['course_description'], 'classes' => []];
        }
        if ($row['class_id'] !== null) {
            $courses_data[$course_id]['classes'][] = $row;
        }
    }
}

?>

<a name="top"></a> <!-- Named Anchor -->
<h1 class="text-center"><i class="fas fa-tachometer-alt"></i> Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
<p class="text-center my-4">Manage courses, classes, videos, and student enrollments.</p>

<div class="flex-group my-4">
    <a href="logout.php" class="button button-secondary link-button"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<?php echo $message; ?>

<!-- Section 1: Add New Course -->
<div class="card">
    <h2><i class="fas fa-folder-plus"></i> Add New Course</h2>
    <form method="POST" action="admin_dashboard.php">
        <input type="hidden" name="add_course" value="1">
        <table class="form-table">
            <tr>
                <td><label for="course_name">Course Name:</label></td>
                <td><input type="text" id="course_name" name="course_name" required></td>
            </tr>
            <tr>
                <td><label for="course_description">Description:</label></td>
                <td><textarea id="course_description" name="course_description" required></textarea></td>
            </tr>
        </table>
        <div class="form-actions">
            <button type="submit" class="button button-primary"><i class="fas fa-save"></i> Add Course</button>
        </div>
    </form>
</div>

<!-- Section 2: Manage Existing Courses & Classes -->
<div class="card">
    <h2><i class="fas fa-list"></i> Manage Courses and Classes</h2>
    <?php if (empty($courses_data)): ?>
        <p class="text-center">No courses have been added yet.</p>
    <?php else: ?>
        <?php foreach ($courses_data as $course_id => $course): ?>
            <div style="border: 1px solid var(--clr-border); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: var(--clr-primary); margin-bottom: 10px;"><?php echo htmlspecialchars($course['name']); ?> 
                    <a href="admin_edit_class.php?action=edit_course&course_id=<?php echo $course_id; ?>" title="Edit Course Name/Description" style="margin-left: 10px; font-size: 0.9em;"><i class="fas fa-edit"></i></a>
                </h3>
                <p style="margin-bottom: 15px;"><?php echo htmlspecialchars($course['desc']); ?></p>

                <h4><i class="fas fa-calendar-alt"></i> Classes:</h4>
                <?php if (!empty($course['classes'])): ?>
                    <table class="data-table" style="font-size: 0.9em;">
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Actions</th>
                                <th>View Enrollments</th>
                                <th>Manage Videos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($course['classes'] as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($class['class_date']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($class['class_time']))); ?></td>
                                    <td>
                                        <!-- Admin can edit the class date and time function -->
                                        <a href="admin_edit_class.php?class_id=<?php echo $class['class_id']; ?>" class="button button-secondary link-button" style="padding: 5px 10px;"><i class="fas fa-clock"></i> Edit Class</a>
                                    </td>
                                    <td>
                                        <a href="admin_dashboard.php?view=enrollments&class_id=<?php echo $class['class_id']; ?>" class="button button-primary link-button" style="padding: 5px 10px;"><i class="fas fa-users"></i> View Enrolled</a>
                                    </td>
                                    <td>
                                        <a href="admin_edit_class.php?action=manage_videos&course_id=<?php echo $course_id; ?>" class="button button-primary link-button" style="padding: 5px 10px;"><i class="fas fa-film"></i> Edit Videos</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="margin-top: 10px;">No classes defined for this course yet.</p>
                <?php endif; ?>
                <div class="form-actions" style="padding-top: 15px;">
                    <a href="admin_edit_class.php?action=add_class&course_id=<?php echo $course_id; ?>" class="button button-primary link-button">
                        <i class="fas fa-calendar-plus"></i> Add New Class
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Section 3: View Enrolled Users (Admin can see the name who are enrolled) -->
<?php if (isset($_GET['view']) && $_GET['view'] === 'enrollments' && isset($_GET['class_id'])): 
    $view_class_id = (int)$_GET['class_id'];
    
    // Fetch class name
    $stmt = $conn->prepare("SELECT c.class_name, co.course_name FROM classes c JOIN courses co ON c.course_id = co.course_id WHERE c.class_id = ?");
    $stmt->bind_param("i", $view_class_id);
    $stmt->execute();
    $class_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch enrolled users
    $sql_enrollments = "SELECT u.full_name, u.email, u.phone, e.enrollment_date 
                        FROM enrollments e 
                        JOIN users u ON e.user_id = u.user_id 
                        WHERE e.class_id = ?";
    $stmt_enrollments = $conn->prepare($sql_enrollments);
    $stmt_enrollments->bind_param("i", $view_class_id);
    $stmt_enrollments->execute();
    $enrollments_result = $stmt_enrollments->get_result();
?>
<a name="enrollments"></a> <!-- Named Anchor -->
<div class="card">
    <h2><i class="fas fa-user-check"></i> Enrolled Users for: <?php echo htmlspecialchars($class_info['course_name'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($class_info['class_name'] ?? 'N/A'); ?></h2>
    
    <?php if ($enrollments_result->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Enrollment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $enrollments_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user['enrollment_date']))); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No students are currently enrolled in this class.</p>
    <?php endif; 
    $stmt_enrollments->close(); ?>
</div>
<?php endif; ?>

<a href="#top" class="back-to-top">Back to Top</a>

<?php
echo get_page_footer();
$conn->close();
?>
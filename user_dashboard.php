<?php
// FILE: user_dashboard.php
// User page to view available courses and classes.

require_once 'db_connect.php';

echo get_page_header("Course Catalog");

// Fetch all courses and their associated classes
$sql = "SELECT c.course_id, c.course_name, c.course_description, 
               cls.class_id, cls.class_name, cls.class_date, cls.class_time 
        FROM courses c 
        LEFT JOIN classes cls ON c.course_id = cls.course_id 
        ORDER BY c.course_name, cls.class_date, cls.class_time";

$result = $conn->query($sql);

$courses_data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $course_id = $row['course_id'];
        if (!isset($courses_data[$course_id])) {
            $courses_data[$course_id] = [
                'name' => $row['course_name'],
                'description' => $row['course_description'],
                'classes' => []
            ];
        }
        if ($row['class_id'] !== null) {
            $courses_data[$course_id]['classes'][] = [
                'id' => $row['class_id'],
                'name' => $row['class_name'],
                'date' => $row['class_date'],
                'time' => $row['class_time']
            ];
        }
    }
}

?>

<h1><i class="fas fa-book-open"></i> Available Courses</h1>
<p class="text-center my-4">Select a class to enroll and view videos for enrolled courses.</p>
<div class="flex-group my-4">
    <a href="contact.php" class="button button-secondary link-button"><i class="fas fa-question-circle"></i> Contact Us</a>
</div>

<?php if (empty($courses_data)): ?>
    <div class="card text-center">
        <p>No courses are currently available. Please check back later.</p>
    </div>
<?php else: ?>
    <?php foreach ($courses_data as $course_id => $course): ?>
        <div class="card">
            <h2><?php echo htmlspecialchars($course['name']); ?></h2>
            <p><?php echo htmlspecialchars($course['description']); ?></p>

            <h3 class="my-4" style="color: var(--clr-text); border-bottom: 1px solid var(--clr-border); padding-bottom: 10px; font-weight: 400;">Upcoming Classes:</h3>
            
            <?php if (empty($course['classes'])): ?>
                <p>No classes scheduled yet for this course.</p>
            <?php else: ?>
                <!-- Proper use of Layout Tables for data display -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Action</th>
                            <th>View Videos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($course['classes'] as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['name']); ?></td>
                                <td><?php echo htmlspecialchars(date('F j, Y', strtotime($class['date']))); ?></td>
                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($class['time']))); ?></td>
                                <td>
                                    <a href="enroll.php?class_id=<?php echo $class['id']; ?>" class="button button-primary link-button" style="padding: 5px 10px;">
                                        <i class="fas fa-plus"></i> Enroll
                                    </a>
                                </td>
                                <td>
                                    <a href="user_view_videos.php?course_id=<?php echo $course_id; ?>" class="button button-secondary link-button" style="padding: 5px 10px;">
                                        <i class="fas fa-video"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<a href="#top" class="back-to-top">Back to Top</a>

<?php
echo get_page_footer();
$conn->close();
?>
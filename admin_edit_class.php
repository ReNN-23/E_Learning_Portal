<?php
// FILE: admin_edit_class.php
// Admin page for editing class dates/times, and managing course videos.

require_once 'db_connect.php';

// Authorization check
if (!is_admin()) {
    redirect('admin_login.php');
}

$action = validate_input($_GET['action'] ?? 'edit_class');
$message = '';
$page_title = 'Admin Content Editor';

// --- Functions to handle form submissions ---

function handle_edit_class_submission($conn) {
    global $message;
    $class_id = (int)$_POST['class_id'];
    $class_name = validate_input($_POST['class_name']);
    $class_date = validate_input($_POST['class_date']);
    $class_time = validate_input($_POST['class_time']);
    $class_link = validate_input($_POST['class_link']);

    if (empty($class_name) || empty($class_date) || empty($class_time)) {
        $message = '<div class="alert-error">Class Name, Date, and Time are required.</div>';
        return;
    }

    $stmt = $conn->prepare("UPDATE classes SET class_name = ?, class_date = ?, class_time = ?, class_link = ? WHERE class_id = ?");
    $stmt->bind_param("ssssi", $class_name, $class_date, $class_time, $class_link, $class_id);

    if ($stmt->execute()) {
        $message = '<div class="alert-success"><i class="fas fa-check"></i> Class details updated successfully!</div>';
    } else {
        $message = '<div class="alert-error">Error updating class: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

function handle_add_class_submission($conn) {
    global $message;
    $course_id = (int)$_POST['course_id'];
    $class_name = validate_input($_POST['class_name']);
    $class_date = validate_input($_POST['class_date']);
    $class_time = validate_input($_POST['class_time']);
    $class_link = validate_input($_POST['class_link']);

    if (empty($class_name) || empty($class_date) || empty($class_time) || $course_id <= 0) {
        $message = '<div class="alert-error">All fields are required.</div>';
        return;
    }

    $stmt = $conn->prepare("INSERT INTO classes (course_id, class_name, class_date, class_time, class_link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $course_id, $class_name, $class_date, $class_time, $class_link);

    if ($stmt->execute()) {
        $message = '<div class="alert-success"><i class="fas fa-check"></i> New Class added successfully!</div>';
    } else {
        $message = '<div class="alert-error">Error adding class: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

function handle_edit_course_submission($conn) {
    global $message;
    $course_id = (int)$_POST['course_id'];
    $course_name = validate_input($_POST['course_name']);
    $course_description = validate_input($_POST['course_description']);

    if (empty($course_name) || empty($course_description) || $course_id <= 0) {
        $message = '<div class="alert-error">Course Name and Description are required.</div>';
        return;
    }

    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, course_description = ? WHERE course_id = ?");
    $stmt->bind_param("ssi", $course_name, $course_description, $course_id);

    if ($stmt->execute()) {
        $message = '<div class="alert-success"><i class="fas fa-check"></i> Course details updated successfully!</div>';
    } else {
        $message = '<div class="alert-error">Error updating course: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

function handle_manage_videos_submission($conn) {
    global $message;
    $course_id = (int)$_POST['course_id'];
    $video_title = validate_input($_POST['video_title']);
    $video_url = validate_input($_POST['video_url']);
    $video_id = isset($_POST['video_id']) ? (int)$_POST['video_id'] : 0;
    
    if (empty($video_title) || empty($video_url) || $course_id <= 0) {
        $message = '<div class="alert-error">Video Title and URL are required.</div>';
        return;
    }
    
    if ($video_id > 0) {
        // Edit video
        $stmt = $conn->prepare("UPDATE course_videos SET video_title = ?, video_url = ? WHERE video_id = ? AND course_id = ?");
        $stmt->bind_param("ssii", $video_title, $video_url, $video_id, $course_id);
    } else {
        // Add new video
        $stmt = $conn->prepare("INSERT INTO course_videos (course_id, video_title, video_url) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $video_title, $video_url);
    }

    if ($stmt->execute()) {
        $message = '<div class="alert-success"><i class="fas fa-check"></i> Video ' . ($video_id > 0 ? 'updated' : 'added') . ' successfully!</div>';
    } else {
        $message = '<div class="alert-error">Error managing video: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}


// --- POST Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_class'])) {
        handle_edit_class_submission($conn);
        $action = 'edit_class'; // Stay on the edit form
    } elseif (isset($_POST['add_class'])) {
        handle_add_class_submission($conn);
        $action = 'add_class'; // Stay on the add form
    } elseif (isset($_POST['update_course'])) {
        handle_edit_course_submission($conn);
        $action = 'edit_course'; // Stay on the edit course form
    } elseif (isset($_POST['manage_videos'])) {
        handle_manage_videos_submission($conn);
        $action = 'manage_videos'; // Stay on video management
    }
}

// --- Dynamic Content Rendering ---

echo get_page_header($page_title);

echo '<div class="card">';
echo '<div class="flex-group" style="justify-content: flex-start; margin-bottom: 20px;">
        <a href="admin_dashboard.php" class="button button-secondary link-button"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
      </div>';
echo $message;

switch ($action) {
    case 'edit_class':
        $class_id = (int)$_GET['class_id'] ?? (int)$_POST['class_id'] ?? 0;
        if ($class_id > 0) {
            $stmt = $conn->prepare("SELECT c.*, co.course_name FROM classes c JOIN courses co ON c.course_id = co.course_id WHERE c.class_id = ?");
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $class = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($class) {
                echo '<h2><i class="fas fa-pencil-alt"></i> Edit Class Schedule</h2>';
                echo '<p class="my-4">Course: <strong>' . htmlspecialchars($class['course_name']) . '</strong></p>';
                
                // Form to edit class name, date, and time
                echo '<form method="POST" action="admin_edit_class.php?action=edit_class">';
                echo '<input type="hidden" name="class_id" value="' . $class_id . '">';
                echo '<input type="hidden" name="update_class" value="1">';
                
                echo '<table class="form-table">';
                echo '<tr><td><label for="class_name">Class Name:</label></td><td><input type="text" id="class_name" name="class_name" value="' . htmlspecialchars($class['class_name']) . '" required></td></tr>';
                echo '<tr><td><label for="class_date">Date:</label></td><td><input type="date" id="class_date" name="class_date" value="' . htmlspecialchars($class['class_date']) . '" required></td></tr>';
                echo '<tr><td><label for="class_time">Time:</label></td><td><input type="time" id="class_time" name="class_time" value="' . htmlspecialchars($class['class_time']) . '" required></td></tr>';
                echo '<tr><td><label for="class_link">Meeting Link (Optional):</label></td><td><input type="text" id="class_link" name="class_link" value="' . htmlspecialchars($class['class_link'] ?? '') . '"></td></tr>';
                echo '</table>';
                
                echo '<div class="form-actions"><button type="submit" class="button button-primary"><i class="fas fa-sync-alt"></i> Update Class</button></div>';
                echo '</form>';
            } else {
                echo '<p class="text-center">Class not found.</p>';
            }
        } else {
            echo '<p class="text-center">Invalid class ID.</p>';
        }
        break;

    case 'add_class':
        $course_id = (int)$_GET['course_id'] ?? (int)$_POST['course_id'] ?? 0;
        if ($course_id > 0) {
            $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $course = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($course) {
                echo '<h2><i class="fas fa-calendar-plus"></i> Add New Class</h2>';
                echo '<p class="my-4">Course: <strong>' . htmlspecialchars($course['course_name']) . '</strong></p>';
                
                echo '<form method="POST" action="admin_edit_class.php?action=add_class">';
                echo '<input type="hidden" name="course_id" value="' . $course_id . '">';
                echo '<input type="hidden" name="add_class" value="1">';
                
                echo '<table class="form-table">';
                echo '<tr><td><label for="class_name">Class Name:</label></td><td><input type="text" id="class_name" name="class_name" required></td></tr>';
                echo '<tr><td><label for="class_date">Date:</label></td><td><input type="date" id="class_date" name="class_date" required></td></tr>';
                echo '<tr><td><label for="class_time">Time:</label></td><td><input type="time" id="class_time" name="class_time" required></td></tr>';
                echo '<tr><td><label for="class_link">Meeting Link (Optional):</label></td><td><input type="text" id="class_link" name="class_link"></td></tr>';
                echo '</table>';
                
                echo '<div class="form-actions"><button type="submit" class="button button-primary"><i class="fas fa-plus"></i> Add Class</button></div>';
                echo '</form>';
            } else {
                echo '<p class="text-center">Course not found.</p>';
            }
        } else {
            echo '<p class="text-center">Invalid course ID.</p>';
        }
        break;

    case 'edit_course':
        $course_id = (int)$_GET['course_id'] ?? (int)$_POST['course_id'] ?? 0;
        if ($course_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $course = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($course) {
                echo '<h2><i class="fas fa-file-signature"></i> Edit Course Name/Description</h2>';
                
                echo '<form method="POST" action="admin_edit_class.php?action=edit_course">';
                echo '<input type="hidden" name="course_id" value="' . $course_id . '">';
                echo '<input type="hidden" name="update_course" value="1">';
                
                echo '<table class="form-table">';
                echo '<tr><td><label for="course_name">Course Name:</label></td><td><input type="text" id="course_name" name="course_name" value="' . htmlspecialchars($course['course_name']) . '" required></td></tr>';
                echo '<tr><td><label for="course_description">Description:</label></td><td><textarea id="course_description" name="course_description" required>' . htmlspecialchars($course['course_description']) . '</textarea></td></tr>';
                echo '</table>';
                
                echo '<div class="form-actions"><button type="submit" class="button button-primary"><i class="fas fa-save"></i> Save Course</button></div>';
                echo '</form>';
            } else {
                echo '<p class="text-center">Course not found.</p>';
            }
        } else {
            echo '<p class="text-center">Invalid course ID.</p>';
        }
        break;

    case 'manage_videos':
        $course_id = (int)$_GET['course_id'] ?? (int)$_POST['course_id'] ?? 0;
        if ($course_id > 0) {
            // Fetch course name
            $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $course = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$course) {
                echo '<p class="text-center">Course not found.</p>';
                break;
            }

            echo '<h2><i class="fas fa-video"></i> Manage Videos for: ' . htmlspecialchars($course['course_name']) . '</h2>';

            // Fetch existing videos
            $videos_sql = "SELECT * FROM course_videos WHERE course_id = ? ORDER BY video_id";
            $stmt_videos = $conn->prepare($videos_sql);
            $stmt_videos->bind_param("i", $course_id);
            $stmt_videos->execute();
            $videos_result = $stmt_videos->get_result();
            $stmt_videos->close();
            
            // --- Existing Videos Table ---
            echo '<h3 style="color: var(--clr-text); margin-top: 30px;">Existing Videos:</h3>';
            if ($videos_result->num_rows > 0) {
                echo '<table class="data-table">';
                echo '<thead><tr><th>Title</th><th>URL</th><th>Action</th></tr></thead>';
                echo '<tbody>';
                while($video = $videos_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($video['video_title']) . '</td>';
                    echo '<td><a href="' . htmlspecialchars($video['video_url']) . '" target="_blank">View Link</a></td>';
                    echo '<td><a href="admin_edit_class.php?action=edit_video_form&video_id=' . $video['video_id'] . '&course_id=' . $course_id . '" class="button button-secondary link-button" style="padding: 5px 10px;"><i class="fas fa-edit"></i> Edit</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No videos added for this course yet.</p>';
            }
            
            // Fall through to display the Add Video form
        } else {
            echo '<p class="text-center">Invalid course ID.</p>';
        }
    
    // Fall through to ADD VIDEO FORM
    case 'edit_video_form':
        $course_id = (int)$_GET['course_id'] ?? (int)$_POST['course_id'] ?? 0;
        $video_id = (int)$_GET['video_id'] ?? 0;
        $video_title = '';
        $video_url = '';
        $form_action_title = 'Add New Video';

        if ($video_id > 0) {
            $form_action_title = 'Edit Video';
            $stmt = $conn->prepare("SELECT video_title, video_url FROM course_videos WHERE video_id = ?");
            $stmt->bind_param("i", $video_id);
            $stmt->execute();
            $video_data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($video_data) {
                $video_title = $video_data['video_title'];
                $video_url = $video_data['video_url'];
            }
        }
        
        echo '<h3 style="color: var(--clr-primary); margin-top: 40px;"><i class="fas fa-plus-square"></i> ' . $form_action_title . '</h3>';

        echo '<form method="POST" action="admin_edit_class.php?action=manage_videos&course_id=' . $course_id . '">';
        echo '<input type="hidden" name="course_id" value="' . $course_id . '">';
        echo '<input type="hidden" name="video_id" value="' . $video_id . '">';
        echo '<input type="hidden" name="manage_videos" value="1">';
        
        echo '<table class="form-table">';
        echo '<tr><td><label for="video_title">Video Title:</label></td><td><input type="text" id="video_title" name="video_title" value="' . htmlspecialchars($video_title) . '" required></td></tr>';
        echo '<tr><td><label for="video_url">Video URL (Embed/Link):</label></td><td><input type="text" id="video_url" name="video_url" value="' . htmlspecialchars($video_url) . '" required></td></tr>';
        echo '</table>';
        
        echo '<div class="form-actions"><button type="submit" class="button button-primary"><i class="fas fa-save"></i> Save Video</button></div>';
        echo '</form>';
        break;

    default:
        echo '<p class="text-center">Invalid action specified.</p>';
        break;
}

echo '</div>';

echo get_page_footer();
$conn->close();
?>
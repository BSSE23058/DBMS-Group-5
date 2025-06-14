<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // ✅ Start session before accessing $_SESSION

// Database connection
$host = "sql12.freesqldatabase.com";
$port = "3306";
$db = "sql12784403";
$user = "sql12784403";
$pass = "WAuJFq9xaX";
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add to my courses
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $user_id = $_SESSION['user_id'] ?? 1; // Replace with actual logged-in user id
    $course_id = intval($_POST['course_id']);
    // Prevent duplicates
    $check = $conn->prepare("SELECT * FROM student_courses WHERE student_id=? AND course_id=?");
    $check->bind_param("ii", $user_id, $course_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $user_id, $course_id);
        if ($stmt->execute()) {
            $message = "Course added to your list!";
        } else {
            $message = "Error adding course.";
        }
    } else {
        $message = "You already added this course.";
    }
}

// Handle course addition (admin or tutor)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = $_POST['title'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $location = $_POST['location'] ?? '';
    $mode = $_POST['mode'] ?? '';
    $fee_type = $_POST['fee_type'] ?? '';
    $fee_amount = $_POST['fee_amount'] ?? '';
    $tutor_id = $_POST['tutor_id'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_url = $_FILES['image']['name'] ?? '';
    $is_approved = 1; // Default value
    $is_active = 1; // Default value

    // Handle image upload
    if ($image_url) {
        $target_dir = "../uploads/courses/";
        $target_file = $target_dir . basename($image_url);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Insert into database
    $sql = "INSERT INTO courses 
    (title, subject, location, mode, fee_type, fee_amount, tutor_id, rating, description, image_url, is_approved, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssdidssii",
        $title,
        $subject,
        $location,
        $mode,
        $fee_type,
        $fee_amount,
        $tutor_id,
        $rating,
        $description,
        $image_url,
        $is_approved,
        $is_active
    );
    if ($stmt->execute()) {
        $message = "Course added successfully!";
    } else {
        $message = "Error adding course: " . $stmt->error;
    }
}

// Fetch all courses
$courses = [];
$result = $conn->query("SELECT c.*, t.name AS tutor_name
FROM courses c
LEFT JOIN tutors t ON c.tutor_id = t.id
WHERE c.is_approved = 1");
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch all tutors for the dropdown
$tutors = [];
$tutor_result = $conn->query("SELECT id, name FROM tutors");
while ($row = $tutor_result->fetch_assoc()) {
    $tutors[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Course - Skill Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        .course-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(34, 3, 89, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            background: #fff;
        }

        .course-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 8px 32px rgba(34, 3, 89, 0.16);
        }

        .course-card img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }

        .course-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #220359;
            min-height: 48px;
        }

        .course-instructor {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .course-meta {
            font-size: 0.9rem;
            color: #888;
        }

        .course-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #4906bf;
        }

        body {
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <!-- Navbar (copy from your template) -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="../logo.jpeg" alt="SkillConnect Logo"
                    style="height: 60px; width: auto; object-fit: contain" />
            </a>
            <div class="collapse navbar-collapse justify-content-between">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="./landing_page.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./all_jobs.html">All Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="./all_courses.html">All Courses</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Services</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="./registration_form_students_tutor.html">Tutoring</a>
                            </li>
                            <li><a class="dropdown-item" href="./registration_form_jobSeekers_Employeers.html">Job
                                    Matching</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="./faq.html">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="./aboutUs.html">About Us</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown menu-dropdown">
                        <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">Menu</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="add_course.php">Add Courses</a></li>
                            <li><a class="dropdown-item" href="my_courses.php">My Courses</a></li>
                            <li><a class="dropdown-item" href="timetable.php">My Timetable</a></li>
                            <li><a class="dropdown-item" href="tutor_feedback.php">Tutor Feedback</a></li>
                            <li><a class="dropdown-item" href="submit_feedback.php">Complain</a></li>
                            <li><a class="dropdown-item" href="chat.php">Chat</a></li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center nav-icons">
                        <div class="nav-buttons">
                            <a href="../logout.php" class="btn btn-primary">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h3 class="fw-bold mb-4 text-center">Choose Courses to Add</h3>
        <?php if ($message): ?>
            <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col">
                    <div class="card course-card h-100">
                        <img src="../<?= htmlspecialchars($course['image_url'] ?? 'uploads/courses/default.jpg') ?>"
                            alt="<?= htmlspecialchars($course['title'] ?? 'Course') ?>" class="card-img-top">
                        <div class="card-body">
                            <div class="course-title mb-1"><?= htmlspecialchars($course['title'] ?? 'Untitled Course') ?>
                            </div>
                            <div class="course-instructor mb-2">
                                <i class="bi bi-person-circle"></i>
                                <?= htmlspecialchars($course['tutor_name'] ?? 'Unknown') ?>
                            </div>
                            <div class="course-meta mb-2">
                                <span><i class="bi bi-people"></i> <?= number_format($course['students'] ?? 0) ?>
                                    students</span>
                                <span class="ms-3"><i class="bi bi-star-fill text-warning"></i>
                                    <?= number_format($course['rating'] ?? 0, 1) ?></span>
                            </div>
                            <div class="course-price mb-2">
                                <?= htmlspecialchars($course['fee_type'] ?? 'Fee') ?>:
                                $<?= number_format($course['fee_amount'] ?? 0, 2) ?>
                            </div>
                            <form method="post" class="d-grid">
                                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                <button type="submit" class="btn btn-outline-primary w-100">Add to My Courses</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer (copy from your template) -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <a class="navbar-brand d-flex align-items-center mb-3" href="#">
                        <img src="../logo.jpeg" alt="SkillConnect Logo" height="60" />
                    </a>
                    <p class="text-muted">
                        Temporary minds. Share skills. Shape the future – with
                        skillConnect.
                    </p>
                    <p class="text-muted small">
                        © 2025 SkillConnect. All rights reserved.
                    </p>
                    <p class="text-muted small mb-0">Group B</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h5 class="text-uppercase fw-bold mb-4">All Pages</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="./landing_page.html" class="text-white text-decoration-none">Home</a>
                        </li>
                        <li class="mb-2"><a href="./all_courses.html" class="text-white text-decoration-none">All
                                Courses</a></li>
                        <li class="mb-2"><a href="./faq.html" class="text-white text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="./aboutUs.html" class="text-white text-decoration-none">About Us</a>
                        </li>
                        <li class="mb-2"><a href="./all_jobs.html" class="text-white text-decoration-none">Jobs</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5 class="text-uppercase fw-bold mb-4">Policies</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Terms and Conditions</a>
                        </li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Refund and Returns
                                Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-secondary" />
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-white mb-0">© 2025 SkillConnect. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="small text-white mb-0">Group B</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
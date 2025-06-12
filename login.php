<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$host = "sql12.freesqldatabase.com";
$port = "3306";
$db = "sql12784403";
$user = "sql12784403";
$pass = "WAuJFq9xaX";

// MySQLi connection
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (empty($username) || empty($password)) {
    die("Please fill all fields.");
}

if ($role === 'student') {
    // Check students table
    $sql = "SELECT * FROM students WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['student_id'];
        $_SESSION['role'] = 'student';
        header("Location: student_dashboard.html");
        exit;
    }
} elseif ($role === 'tutor') {
    // Check tutors table
    $sql = "SELECT * FROM tutors WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'tutor';
        header("Location: tutor_dashboard.html");
        exit;
    }
} elseif ($role === 'employer') {
    // Check employers table
    $sql = "SELECT * FROM employers WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'employer';
        header("Location: employer_dashboard.html");
        exit;
    }
} elseif ($role === 'jobseeker') {
    // Check job_seekers table
    $sql = "SELECT * FROM job_seekers WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'jobseeker';
        header("Location: jobseeker_dashboard.html");
        exit;
    }
} elseif ($role === 'admin') {
    // Check admins table
    $sql = "SELECT * FROM admins WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && $password === $user['password']) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['role'] = 'admin';
        header("Location: ./admin/admin_dashboard.html");
        exit;
    } else {
        // Debugging
        if (!$user) {
            echo "No admin found with that username/email.";
        } elseif ($password !== $user['password']) {
            echo "Password incorrect for admin.";
        }
        exit;
    }
}

echo "Invalid credentials.";
$conn->close();
?>
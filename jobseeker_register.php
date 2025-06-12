<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// DB credentials
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

// Collect data from POST
$username = $_POST['username'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$user_type = $_POST['user_type'] ?? '';
$linked_user_id = !empty($_POST['linked_user_id']) ? $_POST['linked_user_id'] : null;
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Check required fields
if (!$username || !$name || !$email || !$user_type || !$password || !$confirm_password) {
    die("Please fill all required fields.");
}

// Check password match
if ($password !== $confirm_password) {
    die("Passwords do not match.");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Handle resume upload
$resume = null;
if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
    $resume = file_get_contents($_FILES['resume']['tmp_name']);
}

// Prepare SQL
$sql = "INSERT INTO job_seekers (username, name, email, phone, resume, user_type, linked_user_id, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "ssssssis",
    $username,
    $name,
    $email,
    $phone,
    $resume,
    $user_type,
    $linked_user_id,
    $hashed_password
);

// Execute and redirect
if ($stmt->execute()) {
    header("Location: login.html");
    exit;
} else {
    echo "Registration failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
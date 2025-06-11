<?php
$host = "gondola.proxy.rlwy.net";
$port = "49915";
$db = "railway";
$user = "postgres";
$pass = "BGBZmAksCrOBHIwzdPnEOmBKLyfPNRAP";

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Retrieve and sanitize form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['number'] ?? '';
$cnic = $_POST['cnic'] ?? '';
$password = $_POST['password'] ?? '';
$bio = $_POST['bio'] ?? '';
$fee_type = $_POST['fee_type'] ?? '';

// Simple validation
if (empty($name) || empty($email) || empty($phone) || empty($cnic) || empty($password) || empty($fee_type)) {
    die("Please fill all required fields.");
}

// INSERT query (use parameterized query to prevent SQL injection)
$sql = "INSERT INTO tutors (name, email, phone_number, cnic, password, bio, fee_type) 
        VALUES ($1, $2, $3, $4, $5, $6, $7)";

$result = pg_query_params($conn, $sql, [$name, $email, $phone, $cnic, $password, $bio, $fee_type]);

if ($result) {
    echo "Tutor registration successful!";
} else {
    echo "Error: " . pg_last_error($conn);
}

pg_close($conn);
?>

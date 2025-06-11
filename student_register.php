<?php
// DB credentials
$host = "gondola.proxy.rlwy.net";
$port = "49915";
$db = "railway";
$user = "postgres";
$pass = "BGBZmAksCrOBHIwzdPnEOmBKLyfPNRAP";

// PostgreSQL connection
$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Collecting data from POST
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Secure password storage
$date_of_birth = $_POST['date_of_birth'];
$cnic = $_POST['cnic'];
$age = $_POST['age'];
$phone = $_POST['phone'];
$bio = $_POST['bio'];
$academic_history = $_POST['academic_history'];
$country = $_POST['country'];
$province = $_POST['province'];
$city = $_POST['city'];
$area = $_POST['area'];
$street = $_POST['street'];
$postal_code = $_POST['postal_code'];
$agreed_terms = isset($_POST['agreed_terms']) ? 'true' : 'false';

// Optional: photo handling (you can skip this if storing path or not uploading now)
$photo = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $photo = pg_escape_bytea(file_get_contents($_FILES['photo']['tmp_name']));
}

// Build INSERT query
$sql = "INSERT INTO students (
    first_name, last_name, username, email, password, date_of_birth, cnic, age, phone,
    photo, bio, academic_history, country, province, city, area, street, postal_code, agreed_terms
) VALUES (
    $1, $2, $3, $4, $5, $6, $7, $8, $9,
    $10, $11, $12, $13, $14, $15, $16, $17, $18, $19
)";

// Prepare & execute
$result = pg_query_params($conn, $sql, [
    $first_name, $last_name, $username, $email, $password, $date_of_birth, $cnic, $age, $phone,
    $photo, $bio, $academic_history, $country, $province, $city, $area, $street, $postal_code, $agreed_terms
]);

// Response
if ($result) {
    echo "Student registered successfully.";
} else {
    echo "Error: " . pg_last_error($conn);
}

// Close DB connection
pg_close($conn);
?>

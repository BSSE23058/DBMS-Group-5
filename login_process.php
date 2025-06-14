<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start a PHP session
session_start();

require_once 'connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $conn->real_escape_string(htmlspecialchars($_POST['identifier'])); // Can be username or email
    $password = $_POST['password']; // Raw password

    $user_id = null;
    $user_type = null; // 'employer' or 'job_seeker'
    $hashed_password = null;

    $errors = [];

    if (empty($identifier) || empty($password)) {
        $errors[] = "Please enter both username/email and password.";
    }

    if (empty($errors)) {
        // Try to find user in 'employers' table
        $sql_employer = "SELECT id, username, email, password FROM employers WHERE username = ? OR email = ?";
        $stmt_employer = $conn->prepare($sql_employer);
        if ($stmt_employer) {
            $stmt_employer->bind_param("ss", $identifier, $identifier);
            $stmt_employer->execute();
            $result_employer = $stmt_employer->get_result();
            if ($result_employer->num_rows > 0) {
                $employer = $result_employer->fetch_assoc();
                $user_id = $employer['id'];
                $user_type = 'employer';
                $hashed_password = $employer['password'];
            }
            $stmt_employer->close();
        } else {
            $errors[] = "Database error checking employer: " . $conn->error;
        }

        // If not found as an employer, try to find user in 'job_seekers' table
        if (!$user_id && empty($errors)) {
            $sql_job_seeker = "SELECT id, username, email, password FROM job_seekers WHERE username = ? OR email = ?";
            $stmt_job_seeker = $conn->prepare($sql_job_seeker);
            if ($stmt_job_seeker) {
                $stmt_job_seeker->bind_param("ss", $identifier, $identifier);
                $stmt_job_seeker->execute();
                $result_job_seeker = $stmt_job_seeker->get_result();
                if ($result_job_seeker->num_rows > 0) {
                    $job_seeker = $result_job_seeker->fetch_assoc();
                    $user_id = $job_seeker['id'];
                    $user_type = 'job_seeker';
                    $hashed_password = $job_seeker['password'];
                }
                $stmt_job_seeker->close();
            } else {
                $errors[] = "Database error checking job seeker: " . $conn->error;
            }
        }

        // Verify password
        if ($user_id && password_verify($password, $hashed_password)) {
            // Login successful!
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['username'] = ($user_type == 'employer') ? $employer['username'] : $job_seeker['username'];
            
            // Redirect based on user type using PHP header
            if ($user_type == 'employer') {
                header("Location: employer_dashboard.php");
            } else { // job_seeker
                header("Location: jobseeker_dashboard.php");
            }
            exit(); // Important to call exit() after header()
        } else {
            $errors[] = "Invalid username/email or password.";
        }
    }

    // If there are any errors, display them using alert (for failed login only)
    $error_message = implode("\\n", $errors);
    echo "<script>alert('Login failed:\\n" . $error_message . "'); window.location.href='login.html';</script>";

} else {
    // If accessed directly without POST method
    header("Location: login.html");
    exit();
}

$conn->close();
?>

<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to get the logged-in user's ID
session_start();

// Include your database connection file
require_once 'connect.php';

// Check if the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    // If not logged in or not an employer, redirect to login page with an error
    echo "<script>alert('You must be logged in as an employer to post a job.'); window.location.href='login.html';</script>";
    exit();
}

// Get the employer_id from the session
$employer_id = $_SESSION['user_id'];

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and validate input data
    $jobTitle = $conn->real_escape_string(htmlspecialchars($_POST['jobTitle']));
    $jobType = $conn->real_escape_string(htmlspecialchars($_POST['jobType']));
    $locationType = $conn->real_escape_string(htmlspecialchars($_POST['locationType'])); // Maps to 'work_mode'
    $location = $conn->real_escape_string(htmlspecialchars($_POST['location']));
    $salary = $conn->real_escape_string(htmlspecialchars($_POST['salary']));
    $jobDescription = $conn->real_escape_string(htmlspecialchars($_POST['jobDescription']));
    $skillsRequired = $conn->real_escape_string(htmlspecialchars($_POST['skillsRequired'])); // Maps to 'requirements'
    $applicationDeadline = !empty($_POST['applicationDeadline']) ? $conn->real_escape_string($_POST['applicationDeadline']) : NULL; // Use NULL for empty date
    $companyName = $conn->real_escape_string(htmlspecialchars($_POST['companyName']));
    $companyWebsite = !empty($_POST['companyWebsite']) ? $conn->real_escape_string(htmlspecialchars($_POST['companyWebsite'])) : NULL; // Use NULL for empty URL
    $contactEmail = $conn->real_escape_string(htmlspecialchars($_POST['contactEmail']));

    // Hardcoded category_id for now (assuming '1' for 'General')
    $category_id = 1;

    // Default values for new job posts
    $default_post_status = 'Active';
    $default_is_promoted = FALSE; // Boolean false maps to 0 in MySQL TINYINT(1)

    // SQL INSERT statement
    $sql = "INSERT INTO job_postings (
                employer_id, title, description, category_id, requirements, location, work_mode,
                application_deadline, company_name, company_website, contact_email,
                salary, job_type, post_status, is_promoted
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "ississssssssisb", // 1 int (employer_id), 13 strings, 1 boolean
            $employer_id,           // Now dynamically pulled from session
            $jobTitle,              // s: title
            $jobDescription,        // s: description
            $category_id,           // i: category_id
            $skillsRequired,        // s: requirements
            $location,              // s: location
            $locationType,          // s: work_mode
            $applicationDeadline,   // s: application_deadline
            $companyName,           // s: company_name
            $companyWebsite,        // s: company_website
            $contactEmail,          // s: contact_email
            $salary,                // s: salary
            $jobType,               // s: job_type
            $default_post_status,   // s: post_status
            $default_is_promoted    // b: is_promoted
        );

        if ($stmt->execute()) {
            echo "<script>alert('Job posted successfully!'); window.location.href='employer_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error posting job: " . $stmt->error . "'); window.location.href='post_job.html';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "'); window.location.href='post_job.html';</script>";
    }

    $conn->close();

} else {
    // If accessed directly without POST method
    // Also add session check for direct access
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
        echo "<script>alert('You must be logged in as an employer to access this page.'); window.location.href='login.html';</script>";
        exit();
    }
    // If it's a logged-in employer, just display the form
    // No header("Location: post_job.html"); needed here as it implies direct access is fine
}
?>

<?php
session_start();
include "Procedures.php";

$proc = new Procedures();
$conn = $proc->getConnection();

if (isset($_POST['username']) && isset($_POST['password'])) {
    
    function validate($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $username = validate($_POST['username']);
    $password = validate($_POST['password']);

    if (empty($username)) {
        header("Location: loginpage.php?error=Username is required");
        exit();
    } else if (empty($password)) {
        header("Location: loginpage.php?error=Password is required");
        exit();
    } else {
        // Use prepared statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=? LIMIT 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Set session
            $_SESSION['username'] = $row['username'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            // Insert login audit log
            $proc->addAuditLog($_SESSION['id'], 'login', $_SESSION['name']);

            $conn->close();
            header("Location: dashboard.php");
            exit();

        } else {
            header("Location: loginpage.php?error=Incorrect username or password");
            exit();
        }
    }

} else {
    header("Location: loginpage.php");
    exit();
}
?>
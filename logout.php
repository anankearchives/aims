<!-- ============= logout.php ============= -->
<?php
session_start();
include "Procedures.php";

$proc = new Procedures();
$conn = $proc->getConnection();

$name = $_SESSION['name'] ?? 'User';

$proc->addAuditLog($_SESSION['id'], 'logout', $_SESSION['name']);

$conn->close();
session_unset();
session_destroy();

header("refresh:2;url=loginpage.php");
echo "<!DOCTYPE html>
<html>
<head>
    <title>Logging Out</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #fff5f5;
        }
        .logout-message {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class='logout-message'>
        <h2>Logging out, $name...</h2>
        <p>You will be redirected to the login page.</p>
    </div>
</body>
</html>";
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


// Dummy user credentials (replace with database connection in real use)
$users = [
    "admin" => "password123",
    "user" => "pass456"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (isset($users[$username]) && $users[$username] === $password) {
        $_SESSION["user"] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>
                alert('Invalid Username or Password');
                window.location.href='index.html';
              </script>";
    }
}
?>

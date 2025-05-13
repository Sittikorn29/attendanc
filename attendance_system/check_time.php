<?php
session_start();
include 'db.php';

$user_id = $_SESSION["user_id"];

// ตรวจสอบสถานะการเช็คอิน
$result = $conn->query("SELECT * FROM attendance WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
$last_record = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$last_record || $last_record['check_out']) {
        $conn->query("INSERT INTO attendance (user_id, check_in) VALUES ($user_id, NOW())");
    } else {
        $conn->query("UPDATE attendance SET check_out = NOW() WHERE id = " . $last_record['id']);
    }
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>เช็คเวลา</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <form method="POST">
        <button type="submit" class="btn btn-success"><?= (!$last_record || $last_record['check_out']) ? "เช็คเข้า" : "เช็คออก" ?></button>
    </form>
</body>
</html>

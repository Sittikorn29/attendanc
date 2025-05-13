<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] !== 'admin') {
        die("คุณไม่มีสิทธิ์ในการเพิ่มเวร");
    }

    $user_id = $_POST['user_id'];
    $title = $_POST['title'];
    $date = $_POST['date'];

    $sql = "INSERT INTO duties (user_id, title, date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $date);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
}
?>
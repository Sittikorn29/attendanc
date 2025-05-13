<?php
session_start();
include 'db.php';

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $fullname = $_POST['fullname'];
    $position = $_POST['position'];
    $level = $_POST['level'];

    // อัปเดตข้อมูลในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE users SET username = ?, fullname = ?, position = ?, level = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $employee_id, $fullname, $position, $level, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // กลับไปที่หน้าหลักหลังจากบันทึกข้อมูล
        exit();
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
}
?>

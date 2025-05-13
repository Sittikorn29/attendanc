<?php
session_start();
require 'db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // ดึง user_id จาก session
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['leave_reason'];
    $attachment = NULL;

    // อัพโหลดไฟล์แนบ (ถ้ามี)
    if (!empty($_FILES['attachment']['name'])) {
        $target_dir = "uploads/";
        $attachment = time() . "_" . basename($_FILES["attachment"]["name"]);
        $target_file = $target_dir . $attachment;
        move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file);
    }

    // บันทึกข้อมูลลง Database
    $sql = "INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, attachment, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $leave_type, $start_date, $end_date, $reason, $attachment);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "ส่งคำขอลางานสำเร็จ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาด"]);
    }
}
?>

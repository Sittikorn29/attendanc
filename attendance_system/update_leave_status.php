<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "error" => "คุณต้องเข้าสู่ระบบ"]);
    exit();
}

if (isset($_POST['leave_id']) && isset($_POST['status'])) {
    $leave_id = $_POST['leave_id'];
    $status = $_POST['status'];

    // ตรวจสอบสถานะที่รับมา
    if (!in_array($status, ['approved', 'rejected'])) {
        echo json_encode(["success" => false, "error" => "สถานะไม่ถูกต้อง"]);
        exit();
    }

    // อัปเดตสถานะในฐานข้อมูล
    $sql = "UPDATE leave_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $leave_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "ข้อมูลไม่ครบถ้วน"]);
}

$conn->close();
?>

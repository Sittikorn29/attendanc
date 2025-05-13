<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "error" => "คุณต้องเข้าสู่ระบบ"]);
    exit();
}

$user_id = $_SESSION["user_id"];

// ตรวจสอบว่าค่าถูกส่งมาหรือไม่
$leave_type = isset($_POST["leave_type"]) ? $_POST["leave_type"] : null;
$start_date = isset($_POST["start_date"]) ? $_POST["start_date"] : null;
$end_date = isset($_POST["end_date"]) ? $_POST["end_date"] : null;
$reason = isset($_POST["leave_reason"]) ? $_POST["leave_reason"] : null;
$status = "รอดำเนินการ"; 

if (!$leave_type || !$start_date || !$end_date || !$reason) {
    echo json_encode(["success" => false, "error" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
    exit();
}

$attachment_name = null;

// ตรวจสอบไฟล์แนบ
if (!empty($_FILES["attachment"]["name"])) {
    $target_dir = "uploads/leave_attachments/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $attachment_name = time() . "_" . basename($_FILES["attachment"]["name"]);
    $target_file = $target_dir . $attachment_name;

    if (!move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
        echo json_encode(["success" => false, "error" => "อัปโหลดไฟล์ไม่สำเร็จ"]);
        exit();
    }
}

// เพิ่มข้อมูลลงในฐานข้อมูล
$sql = "INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, attachment, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssss", $user_id, $leave_type, $start_date, $end_date, $reason, $attachment_name, $status);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();

?>

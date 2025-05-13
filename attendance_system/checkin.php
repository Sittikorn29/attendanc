<?php
session_start();
include 'db.php';

if (!isset($_SESSION['employee_id'])) {
    die("เกิดข้อผิดพลาด: ไม่พบรหัสพนักงาน");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_SESSION['employee_id'];
    $checkin_time = $_POST['time'];
    $remark = $_POST['remark'] ?? ''; // ถ้าไม่มีหมายเหตุจะส่งค่าเป็นค่าว่าง

    if (empty($checkin_time)) {
        die("เกิดข้อผิดพลาด: ไม่พบข้อมูลเวลา");
    }

    $timestamp_checkin = strtotime($checkin_time);
    $timestamp_late = strtotime("08:30");
    $late_status = ($timestamp_checkin > $timestamp_late) ? "สาย" : "ตรงเวลา";

    $sql = "INSERT INTO attendance (employee_id, checkin_time, status, remark) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error);
    }

    $stmt->bind_param("ssss", $employee_id, $checkin_time, $late_status, $remark);

    if ($stmt->execute()) {
        echo "ลงเวลาสำเร็จ! ($late_status)";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

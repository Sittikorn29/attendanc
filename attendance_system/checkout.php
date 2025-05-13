<?php
session_start();
include 'db.php';

if (!isset($_SESSION['employee_id'])) {
    die("เกิดข้อผิดพลาด: ไม่พบรหัสพนักงาน");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_SESSION['employee_id'];
    $checkout_time = $_POST['time'];
    $remark = $_POST['remark'] ?? ''; // ถ้าไม่มีหมายเหตุจะส่งค่าเป็นค่าว่าง

    if (empty($checkout_time)) {
        die("เกิดข้อผิดพลาด: ไม่พบข้อมูลเวลาออก");
    }

    $sql = "UPDATE attendance SET checkout_time = ?, remark = CONCAT(remark, ' | ', ?) WHERE employee_id = ? AND DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("เกิดข้อผิดพลาดในการเตรียม SQL: " . $conn->error);
    }

    $stmt->bind_param("sss", $checkout_time, $remark, $employee_id);

    if ($stmt->execute()) {
        echo "ออกเวลางานสำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

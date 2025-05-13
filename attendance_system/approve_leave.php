<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'], $_POST['action'])) {
    $leave_id = intval($_POST['id']);
    $action = $_POST['action'] === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ';

    // อัปเดตสถานะการลา
    $stmt = $conn->prepare("UPDATE leave_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $action, $leave_id);
    
    if ($stmt->execute()) {
        // ดึง user_id ของคนที่ลางาน
        $stmt_user = $conn->prepare("SELECT user_id FROM leave_requests WHERE id = ?");
        $stmt_user->bind_param("i", $leave_id);
        $stmt_user->execute();
        $stmt_user->bind_result($user_id);
        $stmt_user->fetch();
        $stmt_user->close();

        // สร้างข้อความแจ้งเตือน
        $message = "สถานะการลาของคุณถูก" . $action;

        // เพิ่มข้อมูลลงตาราง notifications
        $stmt_notify = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'admin_to_user')");
        $stmt_notify->bind_param("is", $user_id, $message);
        $stmt_notify->execute();

        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "invalid";
}
?>

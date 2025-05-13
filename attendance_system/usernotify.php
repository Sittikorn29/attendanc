<?php
session_start();
include 'db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    echo "You need to log in first.";
    exit;
}

$user_id = $_SESSION['user_id'];  // สมมติว่า user_id ถูกเก็บไว้ใน session หลังจากการล็อกอิน

// ดึงข้อมูลการแจ้งเตือนจากฐานข้อมูล
$stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($message, $created_at);

// แสดงผลการแจ้งเตือน

echo "<h3>แจ้งเตือนการลางาน</h3>";
if ($stmt->fetch()) {
    do {
        echo "<div class='notification'>";
        echo "<p><strong>$message</strong></p>";
        echo "<p><em>Received on: " . date('Y-m-d H:i:s', strtotime($created_at)) . "</em></p>";
        echo "</div>";

        // เพิ่มการแจ้งเตือนเสียงและ Toast Notification
        echo "<script>window.onload = function() { playNotificationSound(); showToast('$message'); }</script>";
    } while ($stmt->fetch());
} else {
    echo "<p>No notifications at the moment.</p>";
}

$stmt->close();
$conn->close();
?>

  <link href="notify.css" rel="stylesheet">

<!-- เพิ่มไฟล์เสียงแจ้งเตือน -->
<audio id="notificationSound" src="notification_sound.mp3" preload="auto"></audio>

<!-- ส่วนแสดง Toast Notification -->
<div id="toast" class="toast"></div>

<script>
function playNotificationSound() {
    var sound = document.getElementById("notificationSound");
    sound.play();
}

function showToast(message) {
    var toast = document.getElementById("toast");
    toast.innerHTML = message;
    toast.style.display = "block";
    setTimeout(function() {
        toast.style.display = "none";
    }, 5000); // ซ่อน Toast หลังจาก 5 วินาที
}
</script>

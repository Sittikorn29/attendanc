<?php
session_start();  // เริ่มต้นเซสชัน

// ลบข้อมูลทั้งหมดในเซสชัน
session_unset();  

// ทำลายเซสชัน
session_destroy();

// รีไดเร็กต์ไปยังหน้า login
header("Location: login.php");
exit();  // ป้องกันการทำงานของโค้ดหลังจากนี้
?>

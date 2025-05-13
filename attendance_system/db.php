<?php
$servername = "localhost";
$username = "root"; // หรือชื่อผู้ใช้ของคุณ
$password = ""; // หรือรหัสผ่านของคุณ
$dbname = "attendance_system"; // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

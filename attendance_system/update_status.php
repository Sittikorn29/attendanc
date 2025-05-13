<?php
include("db.php"); // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ดึงสถานะล่าสุด
    $result = mysqli_query($conn, "SELECT status FROM leave_requests WHERE id = 1");
    $row = mysqli_fetch_assoc($result);
    $currentStatus = $row["status"];

    // อัปเดตสถานะเป็น "อนุมัติ" ถ้าสถานะเป็น "รอดำเนินการ"
    if ($currentStatus === "รอดำเนินการ") {
        $query = "UPDATE leave_requests SET status = 'อนุมัติ' WHERE id = 1"; 
        mysqli_query($conn, $query);
    }
}

// ดึงสถานะล่าสุด
$result = mysqli_query($conn, "SELECT status FROM leave_requests WHERE id = 1");
$row = mysqli_fetch_assoc($result);
echo $row["status"];
?>

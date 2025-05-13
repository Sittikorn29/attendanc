<?php
session_start();
include 'db.php';

// ดึงข้อมูลการลา
$sql = "SELECT lr.*, u.fullname FROM leave_requests lr 
        JOIN users u ON lr.user_id = u.id 
        ORDER BY lr.created_at DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed (leave_requests): " . $conn->error);
}

// เพิ่มพนักงาน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_employee'])) {
        $employee_id = $conn->real_escape_string($_POST['employee_id']);
        $fullname = $conn->real_escape_string($_POST['fullname']);
        $position = $conn->real_escape_string($_POST['position']);
        $level = $conn->real_escape_string($_POST['level']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $conn->real_escape_string($_POST['role']);

        // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
        if (empty($employee_id) || empty($fullname) || empty($username) || empty($password)) {
            echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (employee_id, fullname, position, level, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $employee_id, $fullname, $position, $level, $username, $password, $role);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('เพิ่มพนักงานสำเร็จ');</script>";
        }
    }

    // แก้ไขข้อมูลพนักงาน
    if (isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $employee_id = $conn->real_escape_string($_POST['employee_id']);
        $fullname = $conn->real_escape_string($_POST['fullname']);
        $position = $conn->real_escape_string($_POST['position']);
        $level = $conn->real_escape_string($_POST['level']);
        $username = $conn->real_escape_string($_POST['username']);
        $role = $conn->real_escape_string($_POST['role']);

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET employee_id=?, fullname=?, position=?, level=?, username=?, password=?, role=? WHERE id=?");
            $stmt->bind_param("sssssssi", $employee_id, $fullname, $position, $level, $username, $password, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET employee_id=?, fullname=?, position=?, level=?, username=?, role=? WHERE id=?");
            $stmt->bind_param("ssssssi", $employee_id, $fullname, $position, $level, $username, $role, $id);
        }

        $stmt->execute();
        $stmt->close();
    }

    // ลบพนักงาน
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$users = $conn->query("SELECT * FROM users");
if (!$users) {
    die("Query failed (users): " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>รายการลางาน</h2>
    <!-- ปุ่มกลับไปหน้า Admin -->
    <button class="btn btn-secondary mb-3" onclick="window.location.href='dashboard.php'">กลับไปหน้า Admin</button>
    <div class="mt-4"></div>  
    <table class="table table-bordered">
        <tr>
            <th>พนักงาน</th>
            <th>ประเภทการลา</th>
            <th>วันที่เริ่ม</th>
            <th>วันที่สิ้นสุด</th>
            <th>เหตุผล</th>
            <th>ไฟล์แนบ</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
        </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td><?= htmlspecialchars($row['start_date']) ?></td>
                    <td><?= htmlspecialchars($row['end_date']) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td>
                    <?php if ($row['attachment']): ?>
                        <a href="uploads/leave_attachments/<?= htmlspecialchars($row['attachment']) ?>" target="_blank">ดูไฟล์</a>
                    <?php else: ?>
                        ไม่มีไฟล์แนบ
                    <?php endif; ?>
                    </td>
                    <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                    <td>
                        <?php if ($row['status'] == 'รอดำเนินการ'): ?>
                            <button class="btn btn-success approve-btn" data-id="<?= $row['id'] ?>">อนุมัติ</button>
                            <button class="btn btn-danger reject-btn" data-id="<?= $row['id'] ?>">ปฏิเสธ</button>
                        <?php elseif ($row['status'] == 'อนุมัติ'): ?>
                            <span class="badge bg-success">อนุมัติแล้ว</span>
                        <?php elseif ($row['status'] == 'ไม่อนุมัติ'): ?>
                            <span class="badge bg-danger">ไม่อนุมัติ</span>
                        <?php else: ?>
                            <?= ucfirst(htmlspecialchars($row['status'])) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        
      

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Approve button click handler
            $(".approve-btn").on("click", function() {
                var leaveId = $(this).data("id");
                
                // Send AJAX request to approve the leave request
                $.ajax({
                    url: "approve_leave.php",
                    method: "POST",
                    data: { id: leaveId, action: 'approve' },
                    success: function(response) {
                        if (response == "success") {
                            $("button[data-id='" + leaveId + "']").parent().prev().text("อนุมัติแล้ว");
                            $("button[data-id='" + leaveId + "']").hide();
                            $("button[data-id='" + leaveId + "'].reject-btn").hide();
                        } else {
                            alert("Error in approving the leave request.");
                        }
                    }
                });
            });

            // Reject button click handler
            $(".reject-btn").on("click", function() {
                var leaveId = $(this).data("id");
                
                // Send AJAX request to reject the leave request
                $.ajax({
                    url: "approve_leave.php",
                    method: "POST",
                    data: { id: leaveId, action: 'reject' },
                    success: function(response) {
                        if (response == "success") {
                            $("button[data-id='" + leaveId + "']").parent().prev().text("ไม่อนุมัติ");
                            $("button[data-id='" + leaveId + "']").hide();
                            $("button[data-id='" + leaveId + "'].approve-btn").hide();
                        } else {
                            alert("Error in rejecting the leave request.");
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>

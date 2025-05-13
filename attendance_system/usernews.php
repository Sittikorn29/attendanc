<?php
session_start();
include 'db.php'; // เชื่อมต่อฐานข้อมูล

// เพิ่มข่าวสาร
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_news'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);

        if (empty($title) || empty($content)) {
            echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO news (title, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $content);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('เพิ่มข่าวสารสำเร็จ');</script>";
        }
    }

    // แก้ไขข่าวสาร
    if (isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);

        $stmt = $conn->prepare("UPDATE news SET title=?, content=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('แก้ไขข่าวสารสำเร็จ');</script>";
    }

    // ลบข่าวสาร
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM news WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// ดึงข้อมูลทั้งหมด
$news = $conn->query("SELECT * FROM news ORDER BY id ASC");
if (!$news) {
    die("Query failed (news): " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข่าวสาร</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>ข่าวสาร</h2>
    <button class="btn btn-secondary mb-3" style="margin-top: -8px;" onclick="window.location.href='userdashboard.php'">
    กลับไปหน้า Menu
</button>

    <!-- <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addNewsModal">เพิ่มข่าวสาร</button> -->

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>หัวข้อ</th>
                <th>รายละเอียด</th>
                <th>วันที่แก้ไข</th>
                <!-- <th>จัดการ</th> -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $news->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                    <td><?= htmlspecialchars($row['updated_at']) ?></td>
                    <!-- ปุ่มแก้ไขและลบข้อมูลข่าวสาร แต่เป็นไว้เพราะเป็นของ admin -->
                    <!-- <td>
                        <button class="btn btn-warning edit-btn" 
                            data-id="<?= $row['id'] ?>" 
                            data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>" 
                            data-content="<?= htmlspecialchars($row['content'], ENT_QUOTES) ?>" 
                            data-bs-toggle="modal" data-bs-target="#editNewsModal">แก้ไข</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger">ลบ</button>
                        </form> -->
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal: เพิ่ม -->
<div class="modal fade" id="addNewsModal" tabindex="-1" aria-labelledby="addNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNewsModalLabel">เพิ่มข่าวสาร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">หัวข้อ</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" name="add_news" class="btn btn-primary">บันทึก</button>
                </div> -->
            </form>
        </div>
    </div>
</div>

<!-- Modal: แก้ไข
<div class="modal fade" id="editNewsModal" tabindex="-1" aria-labelledby="editNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNewsModalLabel">แก้ไขข่าวสาร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">หัวข้อ</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="content" id="edit_content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div> -->

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on("click", ".edit-btn", function() {
        $("#edit_id").val($(this).data('id'));
        $("#edit_title").val($(this).data('title'));
        $("#edit_content").val($(this).data('content'));
    });
</script> -->
</body>
</html>

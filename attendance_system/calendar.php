<?php
session_start();
include 'db.php';

// เพิ่มวันหยุด
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_calendar'])) {
        $date = $_POST['date'];
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $type = $_POST['type'];

        $stmt = $conn->prepare("INSERT INTO calendar (date, title, description, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $date, $title, $description, $type);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $date = $_POST['date'];
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $type = $_POST['type'];

        $stmt = $conn->prepare("UPDATE calendar SET date=?, title=?, description=?, type=? WHERE id=?");
        $stmt->bind_param("ssssi", $date, $title, $description, $type, $id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM calendar WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

$calendar = $conn->query("SELECT * FROM calendar ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการวันหยุด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>วันหยุดราชการ</h2>
    <button class="btn btn-secondary mb-3" onclick="window.location.href='dashboard.php'">
    กลับไปหน้า Admin
</button>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">เพิ่มวันหยุด</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>วันที่</th>
                <th>หัวข้อ</th>
                <th>คำอธิบาย</th>
                <th>ประเภท</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $calendar->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm edit-btn"
                        data-id="<?= $row['id'] ?>"
                        data-date="<?= $row['date'] ?>"
                        data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                        data-type="<?= $row['type'] ?>"
                        data-bs-toggle="modal" data-bs-target="#editModal">
                        แก้ไข
                    </button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal: เพิ่มวันหยุด -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">เพิ่มวันหยุด</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>วันที่:</label>
        <input type="date" name="date" class="form-control mb-2" required>
        <label>หัวข้อ:</label>
        <input type="text" name="title" class="form-control mb-2" required>
        <label>คำอธิบาย:</label>
        <textarea name="description" class="form-control mb-2" required></textarea>
        <label>ประเภท:</label>
        <select name="type" class="form-control" required>
            <option value="holiday">holiday</option>
            <option value="event">event</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_calendar" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: แก้ไขวันหยุด -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">แก้ไขวันหยุด</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id">
        <label>วันที่:</label>
        <input type="date" name="date" id="edit_date" class="form-control mb-2" required>
        <label>หัวข้อ:</label>
        <input type="text" name="title" id="edit_title" class="form-control mb-2" required>
        <label>คำอธิบาย:</label>
        <textarea name="description" id="edit_description" class="form-control mb-2" required></textarea>
        <label>ประเภท:</label>
        <select name="type" id="edit_type" class="form-control" required>
            <option value="holiday">holiday</option>
            <option value="event">event</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_date').value = this.dataset.date;
        document.getElementById('edit_title').value = this.dataset.title;
        document.getElementById('edit_description').value = this.dataset.description;
        document.getElementById('edit_type').value = this.dataset.type;
    });
});
</script>
</body>
</html>

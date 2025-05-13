<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];
// ดึง employee_id
$user_query = "SELECT employee_id FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user_row = $user_result->fetch_assoc();
$employee_id = $user_row['employee_id'];

// ดึงข้อมูลการเข้างาน
$attendance_query = "SELECT checkin_time FROM attendance WHERE employee_id = '$employee_id'";
$attendance_result = $conn->query($attendance_query);

$total_attendance = 0;
$on_time = 0;
$late = 0;

while ($row = $attendance_result->fetch_assoc()) {
    $checkin_time = $row['checkin_time'];
    $total_attendance++;

    if (strtotime($checkin_time) <= strtotime('08:30:00')) {
        $on_time++;
    } else {
        $late++;
    }
}

// ดึงข้อมูลการลา
$leave_query = "SELECT COUNT(*) as leaves FROM leave_requests WHERE user_id = $user_id";
$leave_result = $conn->query($leave_query)->fetch_assoc();
$leave_count = $leave_result['leaves'];

// คำนวณรวมวันทั้งหมด
$total_days = $total_attendance + $leave_count;

// คำนวณ % และจำนวน
$on_time_percentage = $total_days > 0 ? round(($on_time / $total_days) * 100, 2) : 0;
$late_percentage = $total_days > 0 ? round(($late / $total_days) * 100, 2) : 0;
$leave_percentage = $total_days > 0 ? round(($leave_count / $total_days) * 100, 2) : 0;

// ดึงข้อมูลจากตาราง users
$user_query = "SELECT username, fullname, position, level, profile_picture, employee_id FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user_row = $user_result->fetch_assoc();
$username = $user_row['username'];
$fullname = $user_row['fullname'];
$position = $user_row['position'];
$level = $user_row['level'];
$profile_picture = $user_row['profile_picture'];
$employee_id = $user_row['employee_id'];  // ดึงรหัสพนักงาน

// ดึงข้อมูลการเข้าออกงาน
$attendance_query = "SELECT * FROM attendance WHERE user_id = $user_id ORDER BY id DESC LIMIT 5";
$attendance_result = $conn->query($attendance_query);

// ดึงข้อมูล OT
$ot_query = "SELECT * FROM ot_requests WHERE user_id = $user_id ORDER BY id DESC LIMIT 5";
$ot_result = $conn->query($ot_query);
// ดึงข้อมูลข่าวสารจากฐานข้อมูล
$news_query = "SELECT title, content FROM news ORDER BY created_at DESC LIMIT 5"; // ดึงข่าวสารล่าสุด 5 รายการ
$news_result = $conn->query("SELECT * FROM news ORDER BY id ASC");

// ตรวจสอบการลงเวลา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['checkin_checkout'])) {
        if (!$last_record || $last_record['check_out']) {
            // เช็คอิน
            $sql = "INSERT INTO attendance (user_id, check_in) VALUES (?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        } else {
            // เช็คเอาท์
            $sql = "UPDATE attendance SET check_out = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $last_record['id']);
            $stmt->execute();
        }
        header("Location: dashboard.php");
        exit();
    }
}
// การอัปโหลดรูปโปรไฟล์
if (isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $target_dir = "uploads/";  // โฟลเดอร์ที่เก็บไฟล์
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // ตรวจสอบว่าไฟล์เป็นภาพ
    if (getimagesize($file["tmp_name"]) === false) {
        echo "ไฟล์ไม่ใช่ภาพ.";
        $uploadOk = 0;
    }

    // ตรวจสอบขนาดไฟล์
    if ($file["size"] > 5000000) {  // ขนาดไม่เกิน 5MB
        echo "ขนาดไฟล์ใหญ่เกินไป.";
        $uploadOk = 0;
    }

    // ตรวจสอบว่าไฟล์มีนามสกุลที่ถูกต้อง
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        echo "ขออภัย, เฉพาะไฟล์ภาพ JPG, JPEG, PNG และ GIF เท่านั้นที่สามารถอัปโหลดได้.";
        $uploadOk = 0;
    }

    // ถ้าไม่พบข้อผิดพลาดทั้งหมด
    if ($uploadOk == 0) {
        echo "ขออภัย, ไม่สามารถอัปโหลดไฟล์ได้.";
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            // ถ้าอัปโหลดไฟล์สำเร็จ, บันทึกชื่อไฟล์ในฐานข้อมูล
            $profile_picture = basename($file["name"]);
            $conn->query("UPDATE users SET profile_picture = '$profile_picture' WHERE id = $user_id");
            header("Location: dashboard.php");
            exit();
        } else {
            echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์.";
        }
    }
}
// วันหยุดราชการ
$calendar_sql = "SELECT date, title, type FROM calendar";
$calendar_result = $conn->query($calendar_sql);
$calendar_events = [];
while ($row = $calendar_result->fetch_assoc()) {
    $calendar_events[] = [
        'title' => $row['title'],
        'start' => $row['date'],
        'color' => '#dc3545' // สีแดง
    ];
}

// วันลาของพนักงานที่อนุมัติแล้ว
$leave_sql = "SELECT start_date, end_date, leave_type FROM leave_requests WHERE user_id = $user_id AND status = 'อนุมัติ'";
$leave_result = $conn->query($leave_sql);
while ($row = $leave_result->fetch_assoc()) {
    $start = $row['start_date'];
    $end = date('Y-m-d', strtotime($row['end_date'] . ' +1 day')); // ต้องบวกเพิ่ม 1 วัน สำหรับ FullCalendar
    $calendar_events[] = [
        'title' => 'ลางาน: ' . $row['leave_type'],
        'start' => $start,
        'end' => $end,
        'color' => '#ffc107' // สีเหลือง
    ];
}
// ดึงรหัสผ่านของผู้ใช้จากฐานข้อมูล
$query = "SELECT password FROM users WHERE id = $user_id";
$result = $conn->query($query);
$row = $result->fetch_assoc();

// ตรวจสอบว่าได้ข้อมูลหรือไม่
if ($row) {
    // แสดงความยาวของรหัสผ่านที่ถูก hash
    $hashed_password = $row['password'];
    $password_length = strlen($hashed_password);  // คำนวณความยาวของรหัสผ่าน
} else {
    $password_length = "ไม่พบข้อมูล";
}

$thai_months = [
  "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน",
  "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
  "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
];

date_default_timezone_set("Asia/Bangkok"); // ตั้งค่าโซนเวลาให้ตรงกับประเทศไทย

$day = date("d");
$month = date("m");
$year = date("Y") + 543;
$time = date("H:i");

$thai_date = "$day " . $thai_months[$month] . " $year";
// $thai_date = "$day " . $thai_months[$month] . " $year เวลา $time น.";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href="userdashboard.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</head>
<body>
    
    <div class="container mt-4">
        <div class="header-container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- รูปโปรไฟล์ที่คลิกได้ -->
                <img src="uploads/<?php echo $profile_picture ?: 'default-profile.jpg'; ?>" alt="Profile" class="profile-img" id="profileImg">

                <!-- ปุ่มเลือกไฟล์ซ่อนอยู่ -->
                <input type="file" id="fileInput" style="display: none;" accept="image/*"> 
                <div class="ms-3">
                    <!-- การแสดงรหัสพนักงานในหน้า Dashboard -->
                    <p class="mb-1"><strong>รหัสพนักงาน:</strong> <a href="#" class="text-primary"><?php echo $employee_id; ?></a></p>
                    <p class="mb-1"><strong>ชื่อ:</strong> <span class="text-info"><?php echo $fullname; ?></span></p>
                    <p class="mb-1"><strong>ตำแหน่ง:</strong> <a href="#" class="text-primary"><?php echo $position; ?></a></p>
                    <p class="mb-0"><strong>Level:</strong> <span class="text-success"><?php echo $level; ?></span></p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- ปุ่มแก้ไขโปรไฟล์ -->
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="fa fa-user"></i> โปรไฟล์</button>
                <button class="btn btn-danger btn-custom" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="fa fa-key"></i> รหัสผ่าน</button>
                <button class="btn btn-purple btn-custom"><i class="fa fa-fingerprint"></i> ลายเซ็น</button>
            </div>
            <div class="text-end">
                <p class="mb-1 fs-5 text-primary"><strong><?php echo $thai_date; ?></strong></p>
                <div class="time-box d-flex gap-2">
                    <span class="icon-text text-danger"><i class="fa fa-clock"></i> 08:30:00</span>
                    <span class="icon-text text-danger"><i class="fa fa-clock"></i> 16:30:00</span>
                </div>
                <p class="mb-0 text-info"><i class="fa fa-map-marker-alt"></i> 13.946061 : 100.512563</p>
            </div>
            <div class="d-flex gap-2">
            <button id="checkin-btn" class="btn btn-success btn-custom">
           <i class="fa fa-clock-in"></i> ลงเวลางาน
          </button> 
    <button class="btn btn-info btn-custom" id="leave-btn" data-bs-toggle="modal" data-bs-target="#leaveModal"><i class="fa fa-calendar-alt"></i> ลางาน</button>

<!-- Modal สำหรับกรอกข้อมูลลางาน -->
<div class="modal" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="leaveModalLabel">ฟอร์มลางาน</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="leaveForm" enctype="multipart/form-data" method="post">
          <div class="mb-3">
            <label for="leaveType" class="form-label">ประเภทการลา</label>
            <select id="leaveType" name="leave_type" class="form-select" required>
              <option value="ลากิจ">ลากิจ</option>
              <option value="ลาป่วย">ลาป่วย</option>
              <option value="ลาพักร้อน">ลาพักร้อน</option>
              <option value="ลาบวช">ลาบวช</option>
              <option value="ลาคลอด">ลาคลอด</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="leaveStartDate" class="form-label">วันที่เริ่มต้นลา</label>
            <input type="date" id="leaveStartDate" name="start_date" class="form-control" required>
          </div>

          <div class="mb-3">
             <label for="leaveEndDate" class="form-label">วันที่สิ้นสุดลา</label>
            <input type="date" id="leaveEndDate" name="end_date" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="leaveReason" class="form-label">สาเหตุการลา</label>
            <textarea id="leaveReason" name="leave_reason" class="form-control" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label for="leaveAttachment" class="form-label">แนบเอกสาร (ถ้ามี)</label>
            <input type="file" id="leaveAttachment" name="attachment" class="form-control">
          </div>

          <button type="submit" class="btn btn-primary">ส่งคำขอลา</button>
        </form>
      </div>
    </div>
  </div>
</div>
     
<!-- <form id="statusForm">
    <button class="btn btn-secondary btn-custom" type="button" id="approveButton">
        <i class="fa fa-check"></i> <span id="buttonText">รับรอง</span>
    </button>
    <script src="update_status.js"></script>
</form> -->
            </div>
        </div>
    </div>
        </div>
    </div>
<!-- Modal สำหรับการแสดงรหัสผ่าน -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">รหัสผ่านของคุณ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- แสดงรหัสผ่าน (ไม่สามารถแก้ไขได้) -->
                <p><strong>รหัสผ่าน:</strong> <span id="password-display"><?php echo $password_length; ?> ตัวอักษร</span></p>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<br>
<div class="container">
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card card-news shadow-sm">
        <div class="card-header bg-primary text-white">
          ข่าวสาร
        </div>
        <div class="card-body">
          <ul>
            <?php while ($news_row = $news_result->fetch_assoc()): ?>
              <li><strong><?php echo htmlspecialchars($news_row['title']); ?></strong><br>
                <?php echo htmlspecialchars($news_row['content']); ?>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </div>

        <!-- สถิติการเข้างานออกงาน (%) -->
    <div class="col-md-6">
      <div class="card card-chart shadow-sm d-flex align-items-center justify-content-center">
      <canvas id="attendanceChart" width="200" height="200"></canvas>

</div>
      </div>
    </div>
  </div>
</div>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-chevron-left"></i></button>
  
  <!-- <a href="UserList.php" class="menu-item"><i class="bi bi-people"></i><span class="menu-text">ข้อมูลพนักงาน</span></a> -->
  <a href="userleave.php" class="menu-item"><i class="bi bi-calendar-check"></i><span class="menu-text">รายงานการลา</span></a>
  <!-- <a href="cert.php" class="menu-item"><i class="bi bi-file-earmark-text"></i><span class="menu-text">หนังสือรับรอง</span></a> -->
  <a href="usernotify.php" class="menu-item"><i class="bi bi-bell"></i><span class="menu-text">แจ้งเตือน</span></a>
  <a href="usernews.php" class="menu-item"><i class="bi bi-megaphone"></i><span class="menu-text">ข่าวสาร</span></a>
  <a href="usercalendar.php" class="menu-item">
    <i class="bi bi-calendar"></i>
    <span class="menu-text">ปฏิทินวันหยุดตามราชการ</span>
</a>
  <div class="logout-btn">
    <form action="logout.php" method="post">
      <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-box-arrow-right"></i> <span class="menu-text">ออกจากระบบ</span></button>
    </form>
  </div>
</div>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4>ปฏิทินการลางานและวันหยุดราชการ</h4>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Modal สำหรับแก้ไขข้อมูลโปรไฟล์ -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">แก้ไขข้อมูลโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_profile.php" method="POST">
                    <div class="mb-3">
                            <!-- <label for="employee_id" class="form-label">รหัสพนักงาน</label>
                            เปลี่ยนจาก $username เป็น $employee_id
                            <input type="text" class="form-control" id="employee_id" name="employee_id" value="<?php echo $employee_id; ?>" required>
                        </div> -->
                        <div class="mb-3">
                            <label for="fullname" class="form-label">ชื่อ</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo $fullname; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">ตำแหน่ง</label>
                            <input type="text" class="form-control" id="position" name="position" value="<?php echo $position; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="level" class="form-label">Level</label>
                            <input type="text" class="form-control" id="level" name="level" value="<?php echo $level; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="profileimg.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="checkin.js"></script>
    <script src="leav-btn.js"></script>
    <script src="leaveform.js"></script>
    <script>
    // เมื่อ modal เปิด จะใส่รหัสผ่านที่ถูก hash เข้าไปใน modal
    document.addEventListener("DOMContentLoaded", function() {
        const passwordDisplay = document.getElementById("password-display");
        passwordDisplay.textContent = "<?php echo $hashed_password; ?>";  // ใส่รหัสผ่านที่ดึงจากฐานข้อมูล
    });
</script>

<script>
var ctx = document.getElementById('attendanceChart').getContext('2d');
var attendanceChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['ตรงเวลา', 'มาสาย', 'ลา'],
        datasets: [{
            data: [<?= $on_time_percentage ?>, <?= $late_percentage ?>, <?= $leave_percentage ?>],
            backgroundColor: ['#4CAF50', '#FF9800', '#F44336'],
            hoverBackgroundColor: ['#66BB6A', '#FFB74D', '#E57373']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            title: {
                display: true,
                text: 'สรุปสถิติการเข้าออกงาน (%)'
            }
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',  // เปลี่ยนเป็นการแสดงแบบสัปดาห์
        locale: 'th',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        events: <?= json_encode($calendar_events) ?>
    });
    calendar.render();
});
</script>
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const icon = sidebar.querySelector('.toggle-btn i');
    const isCollapsed = sidebar.classList.toggle("collapsed");
    
    // Toggle icons
    icon.classList.toggle("bi-chevron-left");
    icon.classList.toggle("bi-chevron-right");

    // Save state to localStorage
    localStorage.setItem("sidebarCollapsed", isCollapsed);
  }

  // Restore sidebar state from localStorage
  window.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById("sidebar");
    const icon = sidebar.querySelector('.toggle-btn i');
    const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";

    if (isCollapsed) {
      sidebar.classList.add("collapsed");
      icon.classList.add("bi-chevron-right");
      icon.classList.remove("bi-chevron-left");
    } else {
      sidebar.classList.remove("collapsed");
      icon.classList.add("bi-chevron-left");
      icon.classList.remove("bi-chevron-right");
    }
  });

  // โหลดข้อมูลผ่าน fetch และใส่ใน #mainContent
  document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('click', function () {
      const url = this.getAttribute('data-url');
      if (url) {
        fetch(url)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(data => {
            document.getElementById('mainContent').innerHTML = data;
          })
          .catch(error => {
            document.getElementById('mainContent').innerHTML = `<div class="alert alert-danger">ไม่สามารถโหลดข้อมูลจาก "${url}" ได้</div>`;
            console.error('Error loading page:', error);
          });
      }
    });
  });
</script>

</body>   
</html>

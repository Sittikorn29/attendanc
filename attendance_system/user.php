<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Menu Sidebar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="admin.css" rel="stylesheet">
</head>
<body>

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

<!-- Main Content -->
<!-- <div class="content" id="mainContent">
  <h2>ระบบจัดการพนักงาน</h2>
  <p>เนื้อหาหลักจะแสดงที่นี่...</p>
</div> -->

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

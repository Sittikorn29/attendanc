
document.getElementById("leaveForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("process_leave.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("ส่งคำขอลาสำเร็จ!");
            location.reload();
        } else {
            alert("เกิดข้อผิดพลาด: " + data.error);
        }
    })
    .catch(error => console.error("Error:", error));
});

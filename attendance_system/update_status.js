document.addEventListener("DOMContentLoaded", function () {
    const approveBtn = document.getElementById("approveButton");
    if (approveBtn) {
        approveBtn.addEventListener("click", function () {
            fetch("update_status.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "update=true"
            })
            .then(response => response.text())
            .then(status => {
                if (status === "อนุมัติ") {
                    document.getElementById("buttonText").textContent = "อนุมัติแล้ว";
                    approveBtn.disabled = true;
                } else if (status === "รอดำเนินการ") {
                    document.getElementById("buttonText").textContent = "รับรอง";
                }
                alert("อัปเดตสถานะสำเร็จ: " + status);
            })
            .catch(error => console.error("เกิดข้อผิดพลาด:", error));
        });
    } else {
        console.warn("ไม่พบปุ่ม approveButton ใน DOM");
    }
});

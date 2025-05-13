document.addEventListener("DOMContentLoaded", function () {
    let button = document.getElementById("checkin-btn");

    let isCheckedIn = localStorage.getItem("isCheckedIn") === "true";

    if (isCheckedIn) {
        switchToCheckoutButton();
    }

    function formatTime(date) {
        const h = date.getHours().toString().padStart(2, '0');
        const m = date.getMinutes().toString().padStart(2, '0');
        return `${h}:${m}`;
    }

    // เปลี่ยนเป็นปุ่มออกงาน
    function switchToCheckoutButton() {
        button.textContent = "ออกเวลางาน";
        button.id = "checkout-btn";     
        button.classList.remove("btn-success");
        button.classList.add("btn-danger");
        attachCheckoutEvent();
    }

    // เปลี่ยนกลับเป็นปุ่มลงเวลางาน
    function switchToCheckinButton() {
        button.textContent = "ลงเวลางาน";
        button.id = "checkin-btn";
        button.classList.remove("btn-danger");
        button.classList.add("btn-success");
        attachCheckinEvent();
    }

    function attachCheckinEvent() {
        button.addEventListener("click", function () {
            const now = new Date();
            const time = formatTime(now);
            const remark = prompt("หมายเหตุการลงเวลา (ถ้ามี):", "");

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "checkin.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert(xhr.responseText);
                    localStorage.setItem("isCheckedIn", "true");
                    switchToCheckoutButton();
                }
            };
            xhr.send("time=" + time + "&remark=" + encodeURIComponent(remark));
        });
    }

    function attachCheckoutEvent() {
        button.addEventListener("click", function () {
            const now = new Date();
            const time = formatTime(now);
            const remark = prompt("หมายเหตุการออกงาน (ถ้ามี):", "");  // ตอนนี้แสดงแค่ "หมายเหตุการออกงาน (ถ้ามี)"

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "checkout.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert(xhr.responseText);
                    localStorage.setItem("isCheckedIn", "false");
                    switchToCheckinButton();
                }
            };
            xhr.send("time=" + time + "&remark=" + encodeURIComponent(remark));
        });
    }

    // เริ่มต้นด้วยการผูก event ให้ปุ่มเช็คอิน
    if (!isCheckedIn) {
        attachCheckinEvent();
    }
});

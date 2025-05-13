// <!-- JavaScript to handle profile image upload -->

    document.getElementById('profileImg').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });

    document.getElementById('fileInput').addEventListener('change', function(e) {
        var formData = new FormData();
        formData.append('profile_picture', e.target.files[0]);
        formData.append('submit', true);

        fetch('dashboard.php', {
            method: 'POST',
            body: formData
        }).then(response => response.text()).then(result => {
            location.reload();
        });
    });

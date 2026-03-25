document.addEventListener('DOMContentLoaded', function() {
    var loginForm = document.getElementById('loginForm');
    var errorMsg = document.getElementById('errorMsg');

    loginForm.onsubmit = function(e) {
        e.preventDefault();
        var email = document.getElementById('email').value;
        var password = document.getElementById('password').value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'admin/auth.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    window.location.href = 'admin/dashboard.php';
                } else {
                    errorMsg.innerText = response.message;
                    errorMsg.style.display = 'block';
                }
            }
        };

        xhr.send('email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password));
    };
});
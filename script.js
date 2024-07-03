document.addEventListener('DOMContentLoaded', function() {
    var loginForm = document.getElementById('loginForm');
    var registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault(); // 阻止表單默認提交行為

            // 取得表單數據
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;

            // 發送 AJAX 請求到 login.php
            sendAjaxRequest(username, password, 'login.php');
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault(); // 阻止表單默認提交行為

            // 取得表單數據
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            // 檢查兩次密碼是否相同
            if (password !== confirmPassword) {
                alert("密碼不相符");
                return false;
            }

            // 發送 AJAX 請求到 register.php
            sendAjaxRequest(username, password, 'register.php');
        });
    }

    // 定義發送 AJAX 請求的函數
    function sendAjaxRequest(username, password, endpoint) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);

                        // 根據 alert 的值顯示適當的警告
                        if (response.alert === 'error') {
                            alert('Error\n' + response.message);
                        } else if (response.alert === 'warning') {
                            alert('Warning\n' + response.message);
                        } else {
                            alert('Success\n' + response.message);
                            if (endpoint === 'register.php') {
                                setTimeout(function() {
                                    window.location.href = "login.html";
                                }, 2000); // 2000 毫秒 = 2 秒
                            }
                        }
                    } catch (e) {
                        console.error('JSON 回應錯誤:', e);
                        alert('發生錯誤。請稍後再試。');
                    }
                } else {
                    alert("發生錯誤。請稍後再試。");
                }
            }
        };

        xhr.open("POST", endpoint, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password));

        // 阻止表單提交
        return false;
    }
});

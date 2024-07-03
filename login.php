<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得從前端發送的資料
    $username = $_POST["username"];
    $password = $_POST["password"];

    // 建立到資料庫的連線（請使用實際的資料庫資訊）
    $servername = "localhost";
    $username_db = "資料庫帳號";
    $password_db = "資料庫密碼";
    $dbname = "資料夾名稱";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    // 檢查連線是否成功
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 使用預儲存程序進行查詢
    $stmt = $conn->prepare("SELECT password, loginAttempts, lastAttemptTime FROM users WHERE username = ?");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword, $loginAttempts, $lastAttemptTime);

    // 檢查 fetch 是否成功
    if (!$stmt->fetch()) {
        // 如果查無此用戶名，返回登入失敗
        $response = array("message" => "登入失敗。使用者名稱不存在。", "alert" => "error");
    } else {
        // 驗證密碼
        if (password_verify($password, $hashedPassword)) {
            // 登入成功
            $stmt->close(); // 釋放資源

            // 重置錯誤次數
            $stmt_update = $conn->prepare("UPDATE users SET loginAttempts = 0 WHERE username = ?");
            
            // 檢查 prepare 是否成功
            if ($stmt_update === false) {
                die('MySQL prepare 錯誤: ' . $conn->error);
            }

            $stmt_update->bind_param("s", $username);
            $stmt_update->execute();
            $stmt_update->close();

            $response = array("message" => "登入成功！", "alert" => "success");
        } else {
            // 登入失敗
            $stmt->close(); // 釋放資源
            $currentTimestamp = time();
            $timeDiff = $currentTimestamp - $lastAttemptTime;
            $loginAttempts++;

            // 更新錯誤次數和時間
            $stmt_update = $conn->prepare("UPDATE users SET loginAttempts = ?, lastAttemptTime = ? WHERE username = ?");
            $stmt_update->bind_param("iis", $loginAttempts, $currentTimestamp, $username);
            $stmt_update->execute();
            $stmt_update->close();
            
            if ($timeDiff < 60 && $loginAttempts >= 3) {
                // 如果連續 3 次密碼錯誤，禁止登入 1 分鐘
                $response = array(
                    "message" => "登入失敗。帳戶鎖定 1 分鐘。",
                    "alert" => "warning"
                );
            } else {
                $response = array(
                    "message" => "登入失敗。密碼錯誤。",
                    "alert" => "error"
                );
            }
        }
    }

    // 返回 JSON 格式的數據
    header('Content-Type: application/json');
    echo json_encode($response);

    // 關閉資料庫連線
    $conn->close();
} else {
    // 如果不是 POST 請求，返回錯誤信息
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Method Not Allowed";
}
?>

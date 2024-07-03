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

    // 檢查用戶名是否已經存在
    $stmt_check = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // 用戶名已存在，返回錯誤訊息
        $response = array("message" => "此使用者名稱已存在。請選擇一個不同的使用者名稱。", "alert" => "error");
        header('Content-Type: application/json');
        echo json_encode($response);
        
    }else{
        // 密碼加密處理
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 使用預處理語句插入使用者資訊到資料庫
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute() === TRUE) {
            // 註冊成功
            $response = array("message" => "註冊成功！", "alert" => "success");
        } else {
            // 註冊失敗
            $response = array("message" => "註冊失敗: " . $stmt->error);
        }
        
        // 返回 JSON 格式的數據
        header('Content-Type: application/json');
        echo json_encode($response);

        // 關閉資料庫連線
        $stmt->close();
        $conn->close();
    }
} else {
    // 如果不是 POST 請求，返回錯誤信息
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Method Not Allowed";
}
?>

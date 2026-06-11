<?php
session_start();

// اگر قبلاً لاگین کرده، برو به main.php
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: main.php');
    exit();
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE Username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // بررسی رمز عبور (برای دمو از password_verify استفاده کنید)
        if ($user && ($password === $user['Password'] || password_verify($password, $user['Password']))) {
            // بروزرسانی تاریخ آخرین لاگین
            $updateStmt = $pdo->prepare("UPDATE users SET DateLastLogin = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // ذخیره اطلاعات در سشن
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['Username'];
            $_SESSION['user_firstname'] = $user['FirstName'];
            $_SESSION['user_lastname'] = $user['LastName'];
            $_SESSION['user_position'] = $user['Position'];
            $_SESSION['user_description'] = $user['Description'];
            $_SESSION['user_date1'] = $user['Date1'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            header('Location: main.php');
            exit();
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>ورود به سیستم</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>ورود به سیستم</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="username">نام کاربری</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="input-group">
                    <label for="password">رمز عبور</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">ورود</button>
            </form>
            <div class="demo-info">
                <p>نام کاربری: admin</p>
                <p>رمز عبور: 123456</p>
            </div>
        </div>
    </div>
</body>
</html>
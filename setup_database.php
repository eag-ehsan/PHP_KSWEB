<?php
// فایل setup_database.php - نصب کامل دیتابیس سیستم
// اجرا: http://yourdomain.com/setup_database.php
// ⚠️ بعد از اجرا، این فایل را از روی هاست حذف کنید یا دسترسی به آن را محدود نمایید

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // اتصال به MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html>
    <html lang='fa' dir='rtl'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>نصب دیتابیس</title>
        <style>
            body { font-family: Tahoma, sans-serif; background: #f0f2f5; padding: 40px 20px; direction: rtl; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
            .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #28a745; }
            .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #dc3545; }
            .info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #17a2b8; }
            h1 { color: #667eea; margin-bottom: 20px; }
            hr { margin: 20px 0; }
            .btn { display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-top: 15px; }
            .warning { background: #fff3cd; color: #856404; padding: 12px; border-radius: 8px; margin: 15px 0; border-right: 4px solid #ffc107; }
        </style>
    </head>
    <body>
    <div class='container'>
        <h1>📦 نصب و راه‌اندازی دیتابیس سیستم</h1>
        <hr>";
    
    // ==============================================
    // 1. ساخت دیتابیس company_db
    // ==============================================
    $pdo->exec("CREATE DATABASE IF NOT EXISTS company_db");
    echo "<div class='success'>✅ دیتابیس <strong>company_db</strong> ساخته شد یا از قبل وجود داشت</div>";
    
    // استفاده از دیتابیس
    $pdo->exec("USE company_db");
    
    // ==============================================
    // 2. ساخت جدول users (کاربران)
    // ==============================================
    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        FirstName VARCHAR(50) NOT NULL,
        LastName VARCHAR(50) NOT NULL,
        Username VARCHAR(50) UNIQUE NOT NULL,
        Password VARCHAR(255) NOT NULL,
        Position VARCHAR(100),
        Date1 TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        DateLastLogin DATETIME,
        Description TEXT
    )";
    $pdo->exec($sqlUsers);
    echo "<div class='success'>✅ جدول <strong>users</strong> (کاربران) ساخته شد</div>";
    
    // درج کاربر ادمین (اگر وجود نداشته باشد)
    $checkAdmin = $pdo->query("SELECT * FROM users WHERE Username = 'admin'");
    if ($checkAdmin->rowCount() == 0) {
        $pdo->exec("INSERT INTO users (FirstName, LastName, Username, Password, Position, Description) 
                    VALUES ('مدیر', 'سیستم', 'admin', '123456', 'مدیر ارشد', 'مدیر اصلی سیستم')");
        echo "<div class='success'>👤 کاربر ادمین ساخته شد (نام کاربری: admin / رمز عبور: 123456)</div>";
    } else {
        echo "<div class='info'>⚠️ کاربر admin قبلاً وجود داشت - درج نشد</div>";
    }
    
    // ==============================================
    // 3. ساخت جدول nKind (انواع کالا / منو)
    // ==============================================
    $sqlKind = "CREATE TABLE IF NOT EXISTS nKind (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kind_name VARCHAR(100) NOT NULL UNIQUE,
        kind_order INT DEFAULT 0
    )";
    $pdo->exec($sqlKind);
    echo "<div class='success'>✅ جدول <strong>nKind</strong> (انواع کالا) ساخته شد</div>";
    
    // درج انواع کالا (داده‌های منو)
    $checkKind = $pdo->query("SELECT COUNT(*) FROM nKind");
    $kindCount = $checkKind->fetchColumn();
    
    if ($kindCount == 0) {
        $kinds = ['خوراکی', 'شوینده', 'تنقلات', 'پوشاکی', 'قبض', 'خرید اینترنتی', 'متفرقه'];
        $stmt = $pdo->prepare("INSERT INTO nKind (kind_name, kind_order) VALUES (?, ?)");
        foreach ($kinds as $index => $kind) {
            $stmt->execute([$kind, $index + 1]);
        }
        echo "<div class='success'>📋 داده‌های منو (انواع کالا) درج شدند: " . implode('، ', $kinds) . "</div>";
    } else {
        echo "<div class='info'>⚠️ جدول nKind قبلاً $kindCount رکورد داشت - داده‌ها درج نشد</div>";
    }
    
    // ==============================================
    // 4. ساخت جدول products (خریدها / محصولات)
    // ==============================================
    $sqlProducts = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pName VARCHAR(200) NOT NULL,
        pPrice BIGINT NOT NULL,
        pDate DATE NOT NULL,
        pKind_id INT NOT NULL,
        pUser_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pKind_id) REFERENCES nKind(id) ON DELETE RESTRICT,
        FOREIGN KEY (pUser_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_date (pDate),
        INDEX idx_kind (pKind_id),
        INDEX idx_user (pUser_id)
    )";
    $pdo->exec($sqlProducts);
    echo "<div class='success'>✅ جدول <strong>products</strong> (خریدها) ساخته شد</div>";
    
    // درج نمونه محصولات تستی (اختیاری)
    $checkProducts = $pdo->query("SELECT COUNT(*) FROM products");
    $productCount = $checkProducts->fetchColumn();
    
    if ($productCount == 0) {
        // دریافت id برای نوع‌ها
        $kindsData = $pdo->query("SELECT id, kind_name FROM nKind")->fetchAll(PDO::FETCH_ASSOC);
        $kindMap = [];
        foreach ($kindsData as $k) {
            $kindMap[$k['kind_name']] = $k['id'];
        }
        
        // دریافت id کاربر ادمین
        $userStmt = $pdo->query("SELECT id FROM users WHERE Username = 'admin' LIMIT 1");
        $userId = $userStmt->fetchColumn();
        
        if ($userId && !empty($kindMap)) {
            $sampleProducts = [
                ['برنج ۱۰ کیلویی', 450000, date('Y-m-d'), $kindMap['خوراکی'] ?? 1],
                ['مایع ظرفشویی', 85000, date('Y-m-d'), $kindMap['شوینده'] ?? 2],
                ['پفک نمکی', 25000, date('Y-m-d'), $kindMap['تنقلات'] ?? 3],
                ['تیشرت مردانه', 320000, date('Y-m-d', strtotime('-1 day')), $kindMap['پوشاکی'] ?? 4],
                ['قبض برق', 187000, date('Y-m-d', strtotime('-2 days')), $kindMap['قبض'] ?? 5],
                ['هدفون بلوتوثی', 890000, date('Y-m-d', strtotime('-3 days')), $kindMap['خرید اینترنتی'] ?? 6],
                ['خمیردندان', 35000, date('Y-m-d', strtotime('-1 day')), $kindMap['شوینده'] ?? 2],
            ];
            
            $insertStmt = $pdo->prepare("INSERT INTO products (pName, pPrice, pDate, pKind_id, pUser_id) VALUES (?, ?, ?, ?, ?)");
            foreach ($sampleProducts as $product) {
                $insertStmt->execute([$product[0], $product[1], $product[2], $product[3], $userId]);
            }
            echo "<div class='success'>📊 " . count($sampleProducts) . " نمونه محصول تستی برای نمایش اولیه درج شد</div>";
        } else {
            echo "<div class='info'>⚠️ نمونه محصولات تستی درج نشد (کاربر یا نوع کالا یافت نشد)</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ جدول products قبلاً $productCount رکورد داشت - داده‌های تستی درج نشد</div>";
    }
    
    // ==============================================
    // 5. گزارش نهایی
    // ==============================================
    echo "<hr>";
    echo "<div class='success' style='background:#28a74520; border-right-color:#28a745;'>";
    echo "🎉 <strong>نصب دیتابیس با موفقیت کامل شد!</strong><br>";
    echo "📌 نام دیتابیس: <code>company_db</code><br>";
    echo "👤 کاربر ادمین: <code>admin</code> / رمز عبور: <code>123456</code><br>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "⚠️ <strong>نکته امنیتی مهم:</strong><br>";
    echo "فایل <code>setup_database.php</code> را پس از اجرا از روی هاست خود حذف کنید یا دسترسی به آن را محدود نمایید.";
    echo "</div>";
    
    echo "<a href='index.php' class='btn'>→ ورود به صفحه لاگین</a> ";
    echo "<a href='main.php' class='btn' style='background:#6c757d;'>← رفتن به داشبورد</a>";
    
    echo "</div></body></html>";
    
} catch(PDOException $e) {
    echo "<div class='container'>";
    echo "<div class='error'>❌ خطا در اتصال به دیتابیس: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>🔧 لطفاً اطلاعات اتصال دیتابیس را در ابتدای فایل بررسی کنید (host, user, pass)</div>";
    echo "</div>";
}
?>
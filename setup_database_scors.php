<?php
// ==============================================
// setup_game_scores.php
// اضافه کردن جداول رکوردهای بازی به دیتابیس company_db
// (بدون FOREIGN KEY برای سازگاری با MyISAM)
// ==============================================

// تنظیمات اتصال به دیتابیس
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'company_db';

// ایجاد اتصال
$conn = new mysqli($host, $username, $password, $database);

// بررسی اتصال
if ($conn->connect_error) {
    die("❌ اتصال به دیتابیس失敗: " . $conn->connect_error);
}

echo "✅ اتصال به دیتابیس 'company_db' موفق بود.<br><br>";

// ==============================================
// 1. اضافه کردن ستون به جدول users (اختیاری)
// ==============================================
$sql_alter_users = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS last_game_played VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS last_game_level INT(11) DEFAULT 1,
ADD COLUMN IF NOT EXISTS total_play_time INT(11) DEFAULT 0
";

if ($conn->query($sql_alter_users) === TRUE) {
    echo "✅ ستون‌های جدید به جدول users اضافه شد.<br>";
} else {
    echo "⚠️ خطا در اضافه کردن ستون به users: " . $conn->error . "<br>";
}

// ==============================================
// 2. ایجاد جدول game_scores (بدون FOREIGN KEY)
// ==============================================
$sql_scores = "
CREATE TABLE IF NOT EXISTS game_scores (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    game_name VARCHAR(50) NOT NULL DEFAULT 'ball_gravity',
    level INT(11) NOT NULL,
    score DECIMAL(10,3) NOT NULL,
    score_type ENUM('time', 'points', 'distance') DEFAULT 'time',
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_ip VARCHAR(45) NULL,
    is_best BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_user_game (user_id, game_name),
    INDEX idx_level (level),
    INDEX idx_score (score),
    INDEX idx_best (user_id, game_name, level, is_best)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
";

if ($conn->query($sql_scores) === TRUE) {
    echo "✅ جدول 'game_scores' ایجاد شد.<br>";
} else {
    echo "❌ خطا در ایجاد جدول game_scores: " . $conn->error . "<br>";
}

// ==============================================
// 3. ایجاد جدول high_scores (بدون FOREIGN KEY)
// ==============================================
$sql_highscores = "
CREATE TABLE IF NOT EXISTS high_scores (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    username VARCHAR(50) NOT NULL,
    game_name VARCHAR(50) NOT NULL DEFAULT 'ball_gravity',
    level INT(11) NOT NULL,
    best_score DECIMAL(10,3) NOT NULL,
    score_type ENUM('time', 'points', 'distance') DEFAULT 'time',
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_game_level (game_name, level),
    INDEX idx_best_score (best_score),
    INDEX idx_level (level),
    INDEX idx_user_level (user_id, level),
    UNIQUE KEY unique_user_game_level (user_id, game_name, level)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
";

if ($conn->query($sql_highscores) === TRUE) {
    echo "✅ جدول 'high_scores' ایجاد شد.<br>";
} else {
    echo "❌ خطا در ایجاد جدول high_scores: " . $conn->error . "<br>";
}

// ==============================================
// 4. حذف تریگر قبلی (اگر وجود داشته باشد)
// ==============================================
$conn->query("DROP TRIGGER IF EXISTS update_best_score");

// ==============================================
// 5. نمایش وضعیت نهایی
// ==============================================
echo "<br><hr><br>";
echo "🎮 **تنظیمات دیتابیس بازی با موفقیت انجام شد!** <br><br>";
echo "📊 **جداول ایجاد شده:**<br>";
echo "- game_scores (ذخیره تمام رکوردهای بازی)<br>";
echo "- high_scores (بهترین رکورد هر کاربر برای هر لول)<br><br>";

echo "👥 **کاربران موجود در سیستم (از جدول users):**<br>";
$users_result = $conn->query("SELECT id, Username, FirstName, LastName FROM users LIMIT 10");
if ($users_result && $users_result->num_rows > 0) {
    while($user = $users_result->fetch_assoc()) {
        echo "  - ID: {$user['id']} | Username: {$user['Username']} | نام: {$user['FirstName']} {$user['LastName']}<br>";
    }
} else {
    echo "  - هیچ کاربری یافت نشد<br>";
}

echo "<br><hr><br>";
echo "📝 **راهنمای استفاده:**<br>";
echo "1. در بازی، از user_id همان id کاربر در جدول users استفاده کنید<br>";
echo "2. کاربران موجود: admin (id=1), Dorsa (id=2), Parsa (id=3), Habibeh (id=4)<br>";
echo "3. برای هر لول، زمان رسیدن به هدف (بر حسب ثانیه) ذخیره می‌شود<br>";
echo "4. عدد کوچک‌تر = رکورد بهتر<br>";
echo "5. برای نمایش جدول امتیازات از get_leaderboard.php استفاده کنید<br>";

// بستن اتصال
$conn->close();

echo "<br><br>";
echo "<button onclick='window.close()' style='background:#f4c542; border:none; padding:8px 16px; border-radius:10px; cursor:pointer;'>🔒 بستن این صفحه</button>";
?>
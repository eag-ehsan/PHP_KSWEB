<?php
session_start();

// بررسی امنیتی - فقط ادمین می‌تواند اجرا کند
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SESSION['user_username'] !== 'admin') {
    die('⛔ فقط ادمین می‌تواند این فایل را اجرا کند!');
}

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='fa' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>مهاجرت - اضافه کردن ماژول فروشگاه</title>
    <link rel='stylesheet' href='css/style.css'>
    <style>
        body { background: #f5f7fa; padding: 50px 20px; }
        .setup-container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .success { color: #155724; background: #d4edda; padding: 12px 15px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #28a745; }
        .warning { color: #856404; background: #fff3cd; padding: 12px 15px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #ffc107; }
        .info { color: #0c5460; background: #d1ecf1; padding: 12px 15px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #17a2b8; }
        .error { color: #721c24; background: #f8d7da; padding: 12px 15px; border-radius: 8px; margin: 8px 0; border-right: 4px solid #dc3545; }
        h1 { color: #667eea; margin-bottom: 20px; }
        hr { margin: 20px 0; }
        .step-title { font-size: 18px; font-weight: bold; margin-top: 15px; margin-bottom: 10px; color: #333; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
        .btn-link { display: inline-block; margin-top: 20px; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-left: 10px; }
    </style>
</head>
<body>
<div class='setup-container'>
    <h1>🔄 مهاجرت - اضافه کردن ماژول فروشگاه</h1>
    <p>این فایل جداول جدید را بدون تأثیر بر داده‌های موجود ایجاد می‌کند.</p>
    <hr>";

try {
    // =============================================
    // مرحله 1: ساخت جدول دسته‌بندی کالا (merch_categories)
    // =============================================
    echo "<div class='step-title'>📂 مرحله 1: ساخت جدول دسته‌بندی کالا (merch_categories)</div>";
    
    $sql_categories = "CREATE TABLE IF NOT EXISTS merch_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cat_name VARCHAR(100) NOT NULL UNIQUE,
        cat_slug VARCHAR(100) NOT NULL UNIQUE,
        cat_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_categories);
    echo "<div class='success'>✅ جدول merch_categories ساخته شد</div>";
    
    // درج دسته‌بندی‌های اولیه (فقط اگر جدول خالی باشد)
    $checkCat = $pdo->query("SELECT COUNT(*) FROM merch_categories");
    $catCount = $checkCat->fetchColumn();
    
    if ($catCount == 0) {
        $categories = ['کامپیوتری', 'الکترونیک', 'بهداشتی', 'آرایشی', 'لوازم تحریر', 'اسباب‌بازی', 'ابزار'];
        $stmt = $pdo->prepare("INSERT INTO merch_categories (cat_name, cat_slug, cat_order) VALUES (?, ?, ?)");
        foreach ($categories as $index => $cat) {
            $slug = str_replace(' ', '-', $cat);
            $stmt->execute([$cat, $slug, $index + 1]);
        }
        echo "<div class='success'>✅ دسته‌بندی‌های اولیه درج شد: " . implode('، ', $categories) . "</div>";
    } else {
        echo "<div class='info'>⚠️ جدول merch_categories قبلاً $catCount دسته‌بندی داشت (داده‌ها حفظ شد)</div>";
    }
    
    // =============================================
    // مرحله 2: ساخت جدول اصلی کالاها (merchandise)
    // =============================================
    echo "<div class='step-title'>📦 مرحله 2: ساخت جدول اصلی کالاها (merchandise)</div>";
    
    $sql_merchandise = "CREATE TABLE IF NOT EXISTS merchandise (
        id INT AUTO_INCREMENT PRIMARY KEY,
        merch_name VARCHAR(200) NOT NULL,
        price BIGINT NOT NULL DEFAULT 0,
        warehouse_stock INT NOT NULL DEFAULT 0,
        category_id INT NOT NULL,
        enter_date DATE NOT NULL,
        description TEXT,
        barcode VARCHAR(50) UNIQUE,
        sku VARCHAR(100) UNIQUE,
        is_active TINYINT(1) DEFAULT 1,
        min_stock_alert INT DEFAULT 5,
        weight_gram INT DEFAULT 0,
        dimensions VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES merch_categories(id) ON DELETE RESTRICT,
        INDEX idx_category (category_id),
        INDEX idx_barcode (barcode),
        INDEX idx_sku (sku),
        INDEX idx_is_active (is_active)
    )";
    $pdo->exec($sql_merchandise);
    echo "<div class='success'>✅ جدول merchandise ساخته شد</div>";
    
    // =============================================
    // مرحله 3: اضافه کردن فیلدهای اختیاری (برای عکس و ...)
    // =============================================
    echo "<div class='step-title'>🖼️ مرحله 3: اضافه کردن فیلدهای کمکی</div>";
    
    // چک کردن وجود فیلد image و اضافه کردن اگر不存在 باشد
    try {
        $pdo->exec("ALTER TABLE merchandise ADD COLUMN IF NOT EXISTS image VARCHAR(500) NULL");
        echo "<div class='success'>✅ فیلد image به جدول merchandise اضافه شد</div>";
    } catch(PDOException $e) {
        echo "<div class='info'>ℹ️ فیلد image قبلاً وجود داشت یا نیازی به اضافه شدن نیست</div>";
    }
    
    try {
        $pdo->exec("ALTER TABLE merchandise ADD COLUMN IF NOT EXISTS thumbnail VARCHAR(500) NULL");
        echo "<div class='success'>✅ فیلد thumbnail به جدول merchandise اضافه شد</div>";
    } catch(PDOException $e) {
        echo "<div class='info'>ℹ️ فیلد thumbnail قبلاً وجود داشت</div>";
    }
    
    // =============================================
    // مرحله 4: ایجاد تریگر برای به‌روزرسانی خودکار (اختیاری)
    // =============================================
    echo "<div class='step-title'>⚙️ مرحله 4: ایجاد تریگرهای کمکی</div>";
    
    // تریگر برای لاگ کردن تغییرات موجودی (اختیاری)
    $trigger_sql = "DROP TRIGGER IF EXISTS log_stock_changes";
    $pdo->exec($trigger_sql);
    
    $trigger_sql = "CREATE TRIGGER IF NOT EXISTS log_stock_changes
        AFTER UPDATE ON merchandise
        FOR EACH ROW
        BEGIN
            IF OLD.warehouse_stock != NEW.warehouse_stock THEN
                INSERT INTO stock_log (merchandise_id, old_stock, new_stock, change_date)
                VALUES (OLD.id, OLD.warehouse_stock, NEW.warehouse_stock, NOW());
            END IF;
        END";
    
    // جدول لاگ موجودی (اگر لازم دارید)
    $sql_stock_log = "CREATE TABLE IF NOT EXISTS stock_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        merchandise_id INT NOT NULL,
        old_stock INT NOT NULL,
        new_stock INT NOT NULL,
        change_date DATETIME NOT NULL,
        FOREIGN KEY (merchandise_id) REFERENCES merchandise(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_stock_log);
    echo "<div class='success'>✅ جدول stock_log برای ثبت تغییرات موجودی ساخته شد</div>";
    
    // =============================================
    // نمایش خلاصه
    // =============================================
    echo "<hr>";
    
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM merch_categories")->fetchColumn();
    $merchCount = $pdo->query("SELECT COUNT(*) FROM merchandise")->fetchColumn();
    
    echo "<div class='success' style='background:#d1ecf1; color:#0c5460;'>";
    echo "<h3>📊 خلاصه مهاجرت</h3>";
    echo "<ul style='margin: 10px 0; padding-right: 20px;'>";
    echo "<li>🏷️ تعداد دسته‌بندی‌های کالا: <strong>$categoryCount</strong></li>";
    echo "<li>📦 تعداد کالاهای ثبت‌شده: <strong>$merchCount</strong></li>";
    echo "<li>✅ جدول‌های جدید: merch_categories, merchandise, stock_log</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='success' style='text-align:center; font-size:16px;'>🎉 مهاجرت با موفقیت انجام شد! داده‌های قبلی دست نخورده باقی ماندند.</div>";
    
    echo "<div style='text-align: center; margin-top: 25px;'>";
    echo "<a href='main.php' class='btn-link'>← بازگشت به داشبورد</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ خطا: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
?>
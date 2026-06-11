<?php
session_start();
require_once 'persian_date.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$firstName = $_SESSION['user_firstname'];
$lastName = $_SESSION['user_lastname'];
$currentUserId = $_SESSION['user_id'];

// دریافت لیست انواع کالا برای فیلتر و فرم
$kinds = $pdo->query("SELECT id, kind_name FROM nKind ORDER BY kind_order, id")->fetchAll(PDO::FETCH_ASSOC);

// دریافت لیست کاربران برای فرم
$users = $pdo->query("SELECT id, FirstName, LastName FROM users ORDER BY FirstName")->fetchAll(PDO::FETCH_ASSOC);

// متغیرهای فیلتر
$filterKind = isset($_GET['kind']) ? (int)$_GET['kind'] : 0;
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$message = '';
$messageType = '';

// ثبت کالای جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $pName = trim($_POST['pName'] ?? '');
    $pPrice = (int)($_POST['pPrice'] ?? 0);
    $pDate = $_POST['pDate'] ?? date('Y-m-d');
    $pKind_id = (int)($_POST['pKind_id'] ?? 0);
    $pUser_id = (int)($_POST['pUser_id'] ?? $currentUserId);
    
    if (empty($pName) || $pPrice <= 0 || $pKind_id <= 0) {
        $message = '❌ لطفاً نام کالا، قیمت و نوع کالا را وارد کنید';
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (pName, pPrice, pDate, pKind_id, pUser_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pName, $pPrice, $pDate, $pKind_id, $pUser_id]);
            $message = '✅ کالا با موفقیت ثبت شد';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = '❌ خطا: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// حذف کالا (اختیاری - فقط برای ادمین)
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $_SESSION['user_username'] === 'admin') {
    $deleteId = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$deleteId]);
        $message = '✅ کالا با موفقیت حذف شد';
        $messageType = 'success';
    } catch(PDOException $e) {
        $message = '❌ خطا در حذف: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// ساخت کوئری برای نمایش لیست با فیلتر
$sql = "SELECT p.*, 
        n.kind_name,
        u.FirstName, u.LastName,
        u.Username
        FROM products p
        JOIN nKind n ON p.pKind_id = n.id
        JOIN users u ON p.pUser_id = u.id
        WHERE 1=1";
$params = [];

if ($filterKind > 0) {
    $sql .= " AND p.pKind_id = ?";
    $params[] = $filterKind;
}
if (!empty($filterDateFrom)) {
    $sql .= " AND p.pDate >= ?";
    $params[] = $filterDateFrom;
}
if (!empty($filterDateTo)) {
    $sql .= " AND p.pDate <= ?";
    $params[] = $filterDateTo;
}

$sql .= " ORDER BY p.pDate DESC, p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// محاسبه مجموع قیمت‌ها
$totalPrice = array_sum(array_column($products, 'pPrice'));

// تاریخ جاری شمسی برای نمایش در چاپ
$currentDateShamsi = jdate_simple('Y/m/d - H:i:s', date('Y-m-d H:i:s'));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>مدیریت خریدهای روزانه</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .products-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 12px;
            color: #666;
        }
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .btn-filter, .btn-reset, .btn-print {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-filter {
            background: #667eea;
            color: white;
        }
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        .btn-print {
            background: #17a2b8;
            color: white;
        }
        .btn-print:hover {
            background: #138496;
        }
        .add-form {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-field {
            flex: 1;
            min-width: 150px;
        }
        .form-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 13px;
        }
        .form-field input, .form-field select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .products-table {
            background: white;
            border-radius: 15px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        .total-row {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message.success { background: #d4edda; color: #155724; }
        .message.error { background: #f8d7da; color: #721c24; }
        
        /* استایل دکمه‌های کنار هم */
        .buttons-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .products-container { padding: 10px; }
            th, td { padding: 8px; font-size: 12px; }
            .form-field { min-width: 100%; }
            .buttons-row {
                flex-direction: column;
                align-items: stretch;
            }
            .buttons-row a, .buttons-row button {
                text-align: center;
            }
        }
        .badge-kind {
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
        }
        
        /* ========== استایل مخصوص چاپ ========== */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            /* مخفی کردن المان‌های غیرضروری در چاپ */
            .btn-back,
            .filter-bar,
            .add-form,
            .btn-print,
            .btn-filter,
            .btn-reset,
            .logout-btn,
            .admin-link,
            .btn-delete,
            .buttons-row,
            .dashboard-header,
            header {
                display: none !important;
            }
            
            .products-container {
                padding: 0;
                margin: 0;
            }
            
            .products-table {
                box-shadow: none;
                border-radius: 0;
            }
            
            table {
                border: 1px solid #ddd;
            }
            
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            
            th {
                background: #f0f0f0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .total-row {
                background: #606060;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* نمایش هدر مخصوص چاپ */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                padding: 20px;
                border-bottom: 2px solid #333;
            }
            
            .print-footer {
                display: block !important;
                text-align: center;
                margin-top: 20px;
                padding-top: 10px;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
            }
        }
        
        /* المان‌های مخصوص چاپ (پیش‌فرض مخفی) */
        .print-header, .print-footer {
            display: none;
        }
    </style>
    
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="products-container">
        <!-- هدر مخصوص چاپ (فقط در چاپ نشان داده می‌شود) -->
        <div class="print-header">
            <h2>📋 گزارش خریدهای روزانه</h2>
            <p>تاریخ چاپ: <?php echo $currentDateShamsi; ?></p>
            <hr>
        </div>
        
        <div class="buttons-row">
            <a href="main.php" class="btn-back">← بازگشت به داشبورد</a>
            <button onclick="printReport()" class="btn-print">🖨️ چاپ لیست خریدها</button>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- بخش فیلتر -->
        <div class="filter-bar">
            <h3>🔍 فیلتر خریدها</h3>
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label>نوع کالا</label>
                    <select name="kind">
                        <option value="0">همه</option>
                        <?php foreach ($kinds as $kind): ?>
                            <option value="<?php echo $kind['id']; ?>" <?php echo $filterKind == $kind['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kind['kind_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>از تاریخ (میلادی)</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                    <small style="color:#888;">YYYY-MM-DD</small>
                </div>
                <div class="filter-group">
                    <label>تا تاریخ (میلادی)</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($filterDateTo); ?>">
                    <small style="color:#888;">YYYY-MM-DD</small>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-filter">اعمال فیلتر</button>
                    <a href="products.php" class="btn-reset">حذف فیلتر</a>
                </div>
            </form>
        </div>
        
        <!-- بخش ثبت کالای جدید -->
        <div class="add-form">
            <h3>➕ ثبت خرید جدید</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-field">
                        <label>نام کالا *</label>
                        <input type="text" name="pName" required placeholder="مثال: برنج ۱۰ کیلویی">
                    </div>
                    <div class="form-field">
                        <label>قیمت (تومان) *</label>
                        <input type="number" name="pPrice" required placeholder="مثال: 450000">
                    </div>
                    <div class="form-field">
                        <label>تاریخ خرید (میلادی) *</label>
                        <input type="date" name="pDate" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label>نوع کالا *</label>
                        <select name="pKind_id" required>
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($kinds as $kind): ?>
                                <option value="<?php echo $kind['id']; ?>"><?php echo htmlspecialchars($kind['kind_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>خریدار</label>
                        <select name="pUser_id">
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $currentUserId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-submit">💾 ثبت خرید</button>
            </form>
        </div>
        
        <!-- لیست خریدها -->
        <div class="products-table">
            <h3 style="padding: 20px 20px 0 20px;">📋 لیست خریدها (<?php echo count($products); ?> مورد)</h3>
            <table id="printTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام کالا</th>
                        <th>قیمت (تومان)</th>
                        <th>تاریخ خرید (میلادی)</th>
                        <th>تاریخ خرید (شمسی)</th>
                        <th>نوع کالا</th>
                        <th>خریدار</th>
                        <?php if ($_SESSION['user_username'] === 'admin'): ?>
                            <th>عملیات</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $index => $product): ?>
                            <?php 
                            // تبدیل تاریخ میلادی به شمسی
                            $miladiDate = $product['pDate'];
                            // $shamsiDate = jdate('Y/m/d', strtotime($miladiDate));
                            $shamsiDate = jdate_simple('Y/m/d', $miladiDate);
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($product['pName']); ?></td>
                                <td><?php echo number_format($product['pPrice']); ?></td>
                                <td><?php echo htmlspecialchars($miladiDate); ?></td>
                                <td><?php echo $shamsiDate; ?></td>
                                <td><span class="badge-kind"><?php echo htmlspecialchars($product['kind_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($product['FirstName'] . ' ' . $product['LastName']); ?></td>
                                <?php if ($_SESSION['user_username'] === 'admin'): ?>
                                    <td>
                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('حذف شود؟')">حذف  </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2"><strong>مجموع کل</strong></td>
                            <td colspan="6"><strong><?php echo number_format($totalPrice); ?> تومان</strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                🚫 هیچ خریدی با این فیلترها یافت نشد
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- فوتر مخصوص چاپ (فقط در چاپ نشان داده می‌شود) -->
        <div class="print-footer">
            <p>سیستم مدیریت خریدها - چاپ شده در تاریخ <?php echo $currentDateShamsi; ?></p>
        </div>
    </div>
</body>
</html>
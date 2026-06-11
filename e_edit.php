<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// بررسی دسترسی - فقط ادمین
$isAdmin = ($_SESSION['user_username'] === 'admin');

if (!$isAdmin) {
    header('Location: main.php');
    exit();
}

// ایجاد پوشه آپلود اگر وجود ندارد
$upload_dir = 'uploads/merchandise/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// دریافت لیست دسته‌بندی‌ها
$categories = $pdo->query("SELECT id, cat_name FROM merch_categories ORDER BY cat_order, id")->fetchAll(PDO::FETCH_ASSOC);

// متغیرهای فیلتر
$filterCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filterSearch = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStock = isset($_GET['stock']) ? $_GET['stock'] : '';
$filterActive = isset($_GET['active']) ? (int)$_GET['active'] : 1;

$message = '';
$messageType = '';
$editItem = null;

// =============================================
// تابع آپلود تصویر
// =============================================
function uploadImage($file, $old_image = null) {
    if (!$file || $file['error'] != 0) {
        return $old_image;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }
    
    // حذف تصویر قدیمی اگر وجود داشته باشد
    if ($old_image && file_exists($old_image)) {
        unlink($old_image);
    }
    
    $new_filename = time() . '_' . uniqid() . '.' . $ext;
    $upload_path = 'uploads/merchandise/' . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }
    
    return false;
}

// =============================================
// عملیات حذف
// =============================================
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    try {
        // دریافت مسیر تصاویر برای حذف
        $stmt = $pdo->prepare("SELECT image, thumbnail FROM merchandise WHERE id = ?");
        $stmt->execute([$deleteId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            if ($item['image'] && file_exists($item['image'])) unlink($item['image']);
            if ($item['thumbnail'] && file_exists($item['thumbnail'])) unlink($item['thumbnail']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM merchandise WHERE id = ?");
        $stmt->execute([$deleteId]);
        $message = '✅ کالا با موفقیت حذف شد';
        $messageType = 'success';
    } catch(PDOException $e) {
        $message = '❌ خطا در حذف: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// =============================================
// دریافت اطلاعات برای ویرایش
// =============================================
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM merchandise WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}

// =============================================
// افزودن یا ویرایش کالا
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $merchName = trim($_POST['merch_name'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $warehouse_stock = (int)($_POST['warehouse_stock'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $enter_date = $_POST['enter_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $barcode = trim($_POST['barcode'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $min_stock_alert = (int)($_POST['min_stock_alert'] ?? 5);
    $weight_gram = (int)($_POST['weight_gram'] ?? 0);
    $dimensions = trim($_POST['dimensions'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // آپلود تصاویر
    $image_path = null;
    $thumbnail_path = null;
    
    if ($action === 'edit' && !empty($_POST['item_id'])) {
        $itemId = (int)$_POST['item_id'];
        // دریافت تصاویر قدیمی
        $oldStmt = $pdo->prepare("SELECT image, thumbnail FROM merchandise WHERE id = ?");
        $oldStmt->execute([$itemId]);
        $oldImages = $oldStmt->fetch(PDO::FETCH_ASSOC);
        
        $image_path = uploadImage($_FILES['image'] ?? null, $oldImages['image'] ?? null);
        $thumbnail_path = uploadImage($_FILES['thumbnail'] ?? null, $oldImages['thumbnail'] ?? null);
        
        if ($image_path === false) $image_path = $oldImages['image'] ?? null;
        if ($thumbnail_path === false) $thumbnail_path = $oldImages['thumbnail'] ?? null;
    } else {
        $image_path = uploadImage($_FILES['image'] ?? null);
        $thumbnail_path = uploadImage($_FILES['thumbnail'] ?? null);
    }
    
    if (empty($merchName) || $price <= 0 || $category_id <= 0) {
        $message = '❌ لطفاً نام کالا، قیمت و دسته‌بندی را وارد کنید';
        $messageType = 'error';
    } else {
        try {
            if ($action === 'edit' && !empty($_POST['item_id'])) {
                $itemId = (int)$_POST['item_id'];
                $stmt = $pdo->prepare("UPDATE merchandise SET 
                    merch_name = ?, price = ?, warehouse_stock = ?, category_id = ?, 
                    enter_date = ?, description = ?, barcode = ?, sku = ?, 
                    min_stock_alert = ?, weight_gram = ?, dimensions = ?, is_active = ?,
                    image = ?, thumbnail = ?
                    WHERE id = ?");
                $stmt->execute([$merchName, $price, $warehouse_stock, $category_id, 
                    $enter_date, $description, $barcode, $sku, 
                    $min_stock_alert, $weight_gram, $dimensions, $is_active,
                    $image_path, $thumbnail_path, $itemId]);
                $message = '✅ کالا با موفقیت به‌روزرسانی شد';
                $messageType = 'success';
                $editItem = null;
            } elseif ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO merchandise 
                    (merch_name, price, warehouse_stock, category_id, enter_date, description, 
                     barcode, sku, min_stock_alert, weight_gram, dimensions, is_active,
                     image, thumbnail) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$merchName, $price, $warehouse_stock, $category_id, 
                    $enter_date, $description, $barcode, $sku, 
                    $min_stock_alert, $weight_gram, $dimensions, $is_active,
                    $image_path, $thumbnail_path]);
                $message = '✅ کالای جدید با موفقیت اضافه شد';
                $messageType = 'success';
            }
        } catch(PDOException $e) {
            $message = '❌ خطا: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// =============================================
// افزایش سریع موجودی
// =============================================
if (isset($_POST['quick_stock']) && isset($_POST['item_id']) && is_numeric($_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $addStock = (int)$_POST['add_stock'];
    if ($addStock > 0) {
        $stmt = $pdo->prepare("UPDATE merchandise SET warehouse_stock = warehouse_stock + ? WHERE id = ?");
        $stmt->execute([$addStock, $itemId]);
        $message = '✅ موجودی با موفقیت افزایش یافت';
        $messageType = 'success';
    }
}

// =============================================
// ساخت کوئری برای نمایش لیست با فیلتر
// =============================================
$sql = "SELECT m.*, c.cat_name 
        FROM merchandise m
        JOIN merch_categories c ON m.category_id = c.id
        WHERE 1=1";
$params = [];

if ($filterCategory > 0) {
    $sql .= " AND m.category_id = ?";
    $params[] = $filterCategory;
}
if (!empty($filterSearch)) {
    $sql .= " AND (m.merch_name LIKE ? OR m.barcode LIKE ? OR m.sku LIKE ?)";
    $params[] = "%$filterSearch%";
    $params[] = "%$filterSearch%";
    $params[] = "%$filterSearch%";
}
if ($filterStock === 'low') {
    $sql .= " AND m.warehouse_stock <= m.min_stock_alert AND m.warehouse_stock > 0";
} elseif ($filterStock === 'out') {
    $sql .= " AND m.warehouse_stock = 0";
}
if ($filterActive == 0 || $filterActive == 1) {
    $sql .= " AND m.is_active = ?";
    $params[] = $filterActive;
}

$sql .= " ORDER BY m.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$merchandise = $stmt->fetchAll(PDO::FETCH_ASSOC);

// محاسبه آمار
$totalItems = count($merchandise);
$totalValue = array_sum(array_column($merchandise, 'price'));
$totalStock = array_sum(array_column($merchandise, 'warehouse_stock'));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>مدیریت کالاها</title>
    <link rel="stylesheet" href="css/style.css">
<style>
    .ecommerce-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        padding: 15px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #667eea;
    }
    .stat-label {
        color: #666;
        font-size: 12px;
        margin-top: 5px;
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
    .btn-filter, .btn-reset {
        padding: 8px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
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
    .add-form, .edit-form {
        background: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .form-field {
        margin-bottom: 5px;
    }
    .form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 13px;
    }
    .form-field input, .form-field select, .form-field textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 8px;
    }
    .form-field input[type="file"] {
        padding: 5px;
    }
    .full-width {
        grid-column: 1 / -1;
    }
    .btn-submit, .btn-update {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 10px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 10px;
    }
    .btn-cancel {
        background: #6c757d;
        color: white;
        padding: 10px 30px;
        text-decoration: none;
        border-radius: 8px;
        display: inline-block;
        margin-top: 10px;
        margin-right: 10px;
    }
    
    /* ============================================ */
    /* بخش اصلی جدول - اصلاح شده برای نمایش بهتر */
    /* ============================================ */
    .products-table {
        background: white;
        border-radius: 15px;
        overflow-x: auto;
        overflow-y: visible;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    
    table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    th, td {
        padding: 12px 10px;
        text-align: center;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
    }
    
    th {
        background: #f8f9fa;
        font-weight: bold;
        position: sticky;
        top: 0;
        white-space: nowrap;
        z-index: 10;
    }
    
    td {
        white-space: nowrap;
    }
    
    /* ستون تصویر */
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        background: #f8f9fa;
        display: block;
        margin: 0 auto;
    }
    
    .no-image {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        border-radius: 8px;
        font-size: 20px;
        margin: 0 auto;
    }
    
    /* دکمه‌های عملیات */
    .btn-edit, .btn-delete, .btn-quick-stock {
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 5px;
        font-size: 11px;
        display: inline-block;
        margin: 2px;
        cursor: pointer;
        border: none;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #333;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-quick-stock {
        background: #17a2b8;
        color: white;
        cursor: pointer;
        border: none;
    }
    
    .quick-stock-form {
        display: inline-flex;
        gap: 5px;
        align-items: center;
        margin-top: 5px;
    }
    
    .quick-stock-form input {
        width: 55px;
        padding: 4px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }
    
    /* وضعیت موجودی */
    .stock-low {
        color: #dc3545;
        font-weight: bold;
    }
    .stock-out {
        color: #6c757d;
        font-weight: bold;
    }
    .stock-normal {
        color: #28a745;
    }
    
    /* وضعیت فعال/غیرفعال */
    .badge-active {
        background: #28a745;
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-block;
    }
    .badge-inactive {
        background: #dc3545;
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-block;
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
    
    .message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .message.success { background: #d4edda; color: #155724; }
    .message.error { background: #f8d7da; color: #721c24; }
    
    .image-preview {
        max-width: 80px;
        max-height: 80px;
        margin-top: 10px;
        border-radius: 8px;
        display: block;
    }
    
    /* ستون عملیات */
    td:last-child {
        min-width: 160px;
    }
    
    /* ============================================ */
    /* ریسپانسیو برای موبایل و تبلت */
    /* ============================================ */
    @media (max-width: 992px) {
        .products-table {
            overflow-x: auto;
            display: block;
        }
        table {
            min-width: 850px;
        }
        th, td {
            padding: 8px 6px;
            font-size: 12px;
        }
        .product-image, .no-image {
            width: 40px;
            height: 40px;
        }
        .btn-edit, .btn-delete {
            padding: 3px 6px;
            font-size: 10px;
        }
        .quick-stock-form input {
            width: 45px;
        }
    }
    
    @media (max-width: 768px) {
        .ecommerce-container {
            padding: 10px;
        }
        .form-grid {
            grid-template-columns: 1fr;
        }
        .stats-bar {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .filter-form {
            flex-direction: column;
        }
        .filter-group {
            width: 100%;
        }
        .btn-filter, .btn-reset {
            width: 100%;
            text-align: center;
        }
        table {
            min-width: 750px;
        }
        th, td {
            padding: 6px 4px;
            font-size: 11px;
        }
        td:last-child {
            min-width: 140px;
        }
    }
    
    @media (max-width: 480px) {
        table {
            min-width: 700px;
        }
        .btn-edit, .btn-delete {
            font-size: 9px;
            padding: 2px 5px;
        }
        .badge-active, .badge-inactive {
            font-size: 9px;
            padding: 2px 6px;
        }
    }
    
    /* اسکرول افقی با ظاهر خوب */
    .products-table::-webkit-scrollbar {
        height: 8px;
    }
    
    .products-table::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .products-table::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .products-table::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>  
    
</head>
<body>
    <div class="ecommerce-container">
        <a href="main.php" class="btn-back">← بازگشت به داشبورد</a>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- آمار -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalItems; ?></div>
                <div class="stat-label">تعداد کالاها</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($totalStock); ?></div>
                <div class="stat-label">موجودی کل</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($totalValue); ?></div>
                <div class="stat-label">ارزش کل (تومان)</div>
            </div>
        </div>
        
        <!-- بخش فیلتر -->
        <div class="filter-bar">
            <h3>🔍 فیلتر کالاها</h3>
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label>جستجو</label>
                    <input type="text" name="search" placeholder="نام، بارکد، SKU..." value="<?php echo htmlspecialchars($filterSearch); ?>">
                </div>
                <div class="filter-group">
                    <label>دسته‌بندی</label>
                    <select name="category">
                        <option value="0">همه</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filterCategory == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['cat_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>وضعیت موجودی</label>
                    <select name="stock">
                        <option value="">همه</option>
                        <option value="low" <?php echo $filterStock === 'low' ? 'selected' : ''; ?>>موجودی کم</option>
                        <option value="out" <?php echo $filterStock === 'out' ? 'selected' : ''; ?>>اتمام موجودی</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>وضعیت کالا</label>
                    <select name="active">
                        <option value="1" <?php echo $filterActive == 1 ? 'selected' : ''; ?>>فعال</option>
                        <option value="0" <?php echo $filterActive == 0 ? 'selected' : ''; ?>>غیرفعال</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-filter">اعمال فیلتر</button>
                    <a href="e_edit.php" class="btn-reset">حذف فیلتر</a>
                </div>
            </form>
        </div>
        
        <!-- بخش افزودن کالای جدید -->
        <div class="add-form">
            <h3>➕ افزودن کالای جدید</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-field">
                        <label>نام کالا *</label>
                        <input type="text" name="merch_name" required placeholder="مثال: لپ‌تاپ ایسوس">
                    </div>
                    <div class="form-field">
                        <label>قیمت (تومان) *</label>
                        <input type="number" name="price" required placeholder="0">
                    </div>
                    <div class="form-field">
                        <label>موجودی انبار</label>
                        <input type="number" name="warehouse_stock" value="0">
                    </div>
                    <div class="form-field">
                        <label>دسته‌بندی *</label>
                        <select name="category_id" required>
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['cat_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>تاریخ ثبت</label>
                        <input type="date" name="enter_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-field">
                        <label>بارکد</label>
                        <input type="text" name="barcode" placeholder="بارکد کالا">
                    </div>
                    <div class="form-field">
                        <label>SKU</label>
                        <input type="text" name="sku" placeholder="کد شناسایی">
                    </div>
                    <div class="form-field">
                        <label>هشدار موجودی کم</label>
                        <input type="number" name="min_stock_alert" value="5">
                    </div>
                    <div class="form-field">
                        <label>وزن (گرم)</label>
                        <input type="number" name="weight_gram" value="0">
                    </div>
                    <div class="form-field">
                        <label>ابعاد</label>
                        <input type="text" name="dimensions" placeholder="مثال: 20x30x10">
                    </div>
                    <div class="form-field">
                        <label>تصویر کالا</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small>حداکثر 2 مگابایت</small>
                    </div>
                    <div class="form-field">
                        <label>تصویر بند انگشتی</label>
                        <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small>حداکثر 2 مگابایت</small>
                    </div>
                    <div class="form-field">
                        <label>
                            <input type="checkbox" name="is_active" checked> فعال
                        </label>
                    </div>
                    <div class="form-field full-width">
                        <label>توضیحات</label>
                        <textarea name="description" rows="2" placeholder="توضیحات کالا"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-submit">💾 ذخیره کالا</button>
            </form>
        </div>
        
        <!-- فرم ویرایش -->
        <?php if ($editItem): ?>
        <div class="edit-form">
            <h3>✏️ ویرایش کالا: <?php echo htmlspecialchars($editItem['merch_name']); ?></h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="item_id" value="<?php echo $editItem['id']; ?>">
                <div class="form-grid">
                    <div class="form-field">
                        <label>نام کالا *</label>
                        <input type="text" name="merch_name" required value="<?php echo htmlspecialchars($editItem['merch_name']); ?>">
                    </div>
                    <div class="form-field">
                        <label>قیمت (تومان) *</label>
                        <input type="number" name="price" required value="<?php echo $editItem['price']; ?>">
                    </div>
                    <div class="form-field">
                        <label>موجودی انبار</label>
                        <input type="number" name="warehouse_stock" value="<?php echo $editItem['warehouse_stock']; ?>">
                    </div>
                    <div class="form-field">
                        <label>دسته‌بندی *</label>
                        <select name="category_id" required>
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $editItem['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>تاریخ ثبت</label>
                        <input type="date" name="enter_date" value="<?php echo $editItem['enter_date']; ?>">
                    </div>
                    <div class="form-field">
                        <label>بارکد</label>
                        <input type="text" name="barcode" value="<?php echo htmlspecialchars($editItem['barcode'] ?? ''); ?>">
                    </div>
                    <div class="form-field">
                        <label>SKU</label>
                        <input type="text" name="sku" value="<?php echo htmlspecialchars($editItem['sku'] ?? ''); ?>">
                    </div>
                    <div class="form-field">
                        <label>هشدار موجودی کم</label>
                        <input type="number" name="min_stock_alert" value="<?php echo $editItem['min_stock_alert'] ?? 5; ?>">
                    </div>
                    <div class="form-field">
                        <label>وزن (گرم)</label>
                        <input type="number" name="weight_gram" value="<?php echo $editItem['weight_gram'] ?? 0; ?>">
                    </div>
                    <div class="form-field">
                        <label>ابعاد</label>
                        <input type="text" name="dimensions" value="<?php echo htmlspecialchars($editItem['dimensions'] ?? ''); ?>">
                    </div>
                    <div class="form-field">
                        <label>تصویر کالا</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if (!empty($editItem['image'])): ?>
                            <img src="<?php echo $editItem['image']; ?>" class="image-preview">
                        <?php endif; ?>
                        <small>آپلود تصویر جدید جایگزین قبلی می‌شود</small>
                    </div>
                    <div class="form-field">
                        <label>تصویر بند انگشتی</label>
                        <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if (!empty($editItem['thumbnail'])): ?>
                            <img src="<?php echo $editItem['thumbnail']; ?>" class="image-preview">
                        <?php endif; ?>
                        <small>آپلود تصویر جدید جایگزین قبلی می‌شود</small>
                    </div>
                    <div class="form-field">
                        <label>
                            <input type="checkbox" name="is_active" <?php echo ($editItem['is_active'] ?? 1) ? 'checked' : ''; ?>> فعال
                        </label>
                    </div>
                    <div class="form-field full-width">
                        <label>توضیحات</label>
                        <textarea name="description" rows="2"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-update">💾 ذخیره تغییرات</button>
                <a href="e_edit.php" class="btn-cancel">❌ انصراف</a>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- لیست کالاها -->
        <div class="products-table">
            <h3 style="padding: 20px 20px 0 20px;">📋 لیست کالاها (<?php echo count($merchandise); ?> کالا)</h3>
            <table>
                <thead>
                    <tr>
                        <th>تصویر</th>
                        <th>#</th>
                        <th>نام کالا</th>
                        <th>قیمت (تومان)</th>
                        <th>موجودی</th>
                        <th>دسته‌بندی</th>
                        <th>بارکد/SKU</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                
        
                
                <tbody>
                <?php if (count($merchandise) > 0): ?>
                    <?php foreach ($merchandise as $index => $item): ?>
                        <?php
                        $stockClass = '';
                        $stockText = number_format($item['warehouse_stock']);
                        if ($item['warehouse_stock'] == 0) {
                            $stockClass = 'stock-out';
                            $stockText .= ' (اتمام)';
                        } elseif ($item['warehouse_stock'] <= $item['min_stock_alert']) {
                            $stockClass = 'stock-low';
                            $stockText .= ' (کم)';
                        } else {
                            $stockClass = 'stock-normal';
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                    <img src="<?php echo $item['image']; ?>" class="product-image">
                                <?php elseif (!empty($item['thumbnail']) && file_exists($item['thumbnail'])): ?>
                                    <img src="<?php echo $item['thumbnail']; ?>" class="product-image">
                                <?php else: ?>
                                    <div class="no-image">📷</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['merch_name']); ?></td>
                            <td><?php echo number_format($item['price']); ?></td>
                            <td class="<?php echo $stockClass; ?>"><?php echo $stockText; ?></td>
                            <td><?php echo htmlspecialchars($item['cat_name']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($item['barcode'] ?? '-'); ?>
                                <?php if (!empty($item['sku'])): ?>
                                    <br><small><?php echo htmlspecialchars($item['sku']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['is_active']): ?>
                                    <span class="badge-active">فعال</span>
                                <?php else: ?>
                                    <span class="badge-inactive">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $item['id']; ?>" class="btn-edit">✏️ ویرایش</a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn-delete" onclick="return confirm('آیا از حذف این کالا مطمئن هستید؟')">🗑️ حذف</a>
                                <form method="POST" action="" class="quick-stock-form">
                                    <input type="hidden" name="quick_stock" value="1">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="add_stock" value="1" min="1" style="width: 60px;">
                                    <button type="submit" class="btn-quick-stock">+ افزایش</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            🚫 هیچ کالایی با این فیلترها یافت نشد
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
                            
                
            </table>
        </div>
    </div>
</body>
</html>
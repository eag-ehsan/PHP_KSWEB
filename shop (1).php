<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// دریافت لیست دسته‌بندی‌ها برای فیلتر
$categories = $pdo->query("SELECT id, cat_name FROM merch_categories ORDER BY cat_order, id")->fetchAll(PDO::FETCH_ASSOC);

// متغیرهای فیلتر
$filterCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filterSearch = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterMinPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$filterMaxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;

// سبد خرید (در سشن ذخیره می‌شود)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// =============================================
// اضافه کردن به سبد خرید
// =============================================
if (isset($_GET['add_to_cart']) && is_numeric($_GET['add_to_cart'])) {
    $productId = (int)$_GET['add_to_cart'];
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    
    // بررسی موجودی کالا
    $stmt = $pdo->prepare("SELECT id, merch_name, price, warehouse_stock FROM merchandise WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product && $product['warehouse_stock'] >= $quantity) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $product['id'],
                'name' => $product['merch_name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        $message = '✅ ' . htmlspecialchars($product['merch_name']) . ' به سبد خرید اضافه شد';
        $messageType = 'success';
    } else {
        $message = '❌ موجودی کالا کافی نیست';
        $messageType = 'error';
    }
}

// =============================================
// حذف از سبد خرید
// =============================================
if (isset($_GET['remove_from_cart']) && is_numeric($_GET['remove_from_cart'])) {
    $productId = (int)$_GET['remove_from_cart'];
    unset($_SESSION['cart'][$productId]);
    $message = '✅ کالا از سبد خرید حذف شد';
    $messageType = 'success';
}

// =============================================
// به‌روزرسانی تعداد در سبد خرید
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $id = (int)$id;
        $qty = max(0, (int)$qty);
        if ($qty > 0) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    $message = '✅ سبد خرید به‌روزرسانی شد';
    $messageType = 'success';
}

// =============================================
// محاسبه مجموع سبد خرید
// =============================================
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

// =============================================
// ساخت کوئری برای نمایش لیست کالاها
// =============================================
$sql = "SELECT m.*, c.cat_name 
        FROM merchandise m
        JOIN merch_categories c ON m.category_id = c.id
        WHERE m.is_active = 1 AND m.warehouse_stock > 0";
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
if ($filterMinPrice > 0) {
    $sql .= " AND m.price >= ?";
    $params[] = $filterMinPrice;
}
if ($filterMaxPrice > 0) {
    $sql .= " AND m.price <= ?";
    $params[] = $filterMaxPrice;
}

$sql .= " ORDER BY m.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>فروشگاه - خرید کالا</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .shop-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* سبد خرید */
        .cart-sidebar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        .cart-title {
            font-size: 20px;
            margin-bottom: 15px;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .cart-item-info {
            flex: 2;
        }
        .cart-item-name {
            font-weight: bold;
        }
        .cart-item-price {
            font-size: 12px;
            color: #666;
        }
        .cart-item-quantity {
            width: 60px;
            text-align: center;
        }
        .cart-item-quantity input {
            width: 50px;
            padding: 5px;
            text-align: center;
        }
        .cart-item-total {
            font-weight: bold;
            color: #28a745;
            min-width: 80px;
            text-align: left;
        }
        .cart-remove {
            color: #dc3545;
            text-decoration: none;
            margin-right: 10px;
        }
        .cart-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            font-size: 18px;
            font-weight: bold;
            text-align: left;
        }
        .btn-checkout {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        .empty-cart {
            text-align: center;
            color: #888;
            padding: 20px;
        }
        
        /* فیلترها */
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
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
        .filter-group input, .filter-group select {
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
        }
        .btn-filter {
            background: #667eea;
            color: white;
        }
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        
        /* محصولات */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: co;
            object-position: center;
            background: #f8f9fa;
            display: block;
        }
        .no-image {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            font-size: 48px;
            flex-shrink: 0;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            min-height: 50px;
        }
        .product-price {
            font-size: 20px;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .product-category {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .product-stock {
            font-size: 12px;
            margin-bottom: 15px;
        }
        .stock-normal { color: #28a745; }
        .stock-low { color: #ffc107; }
        .btn-add-to-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-add-to-cart:hover {
            opacity: 0.9;
        }
        .quantity-input {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .quantity-input input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
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
        
        /* دو ستونه برای دسکتاپ */
        .two-columns {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .cart-column {
            width: 350px;
            flex-shrink: 0;
        }
        .products-column {
            flex: 1;
        }
        
        @media (max-width: 992px) {
            .two-columns {
                flex-direction: column;
            }
            .cart-column {
                width: 100%;
            }
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .shop-container {
                padding: 10px;
            }
            .filter-form {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="shop-container">
        <a href="main.php" class="btn-back">← بازگشت به داشبورد</a>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="two-columns">
            <!-- سبد خرید -->
            <div class="cart-column">
                <div class="cart-sidebar">
                    <div class="cart-title">🛒 سبد خرید</div>
                    
                    <?php if (count($_SESSION['cart']) > 0): ?>
                        <form method="POST" action="">
                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                <div class="cart-item">
                                    <div class="cart-item-info">
                                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="cart-item-price"><?php echo number_format($item['price']); ?> تومان</div>
                                    </div>
                                    <div class="cart-item-quantity">
                                        <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="99">
                                    </div>
                                    <div class="cart-item-total">
                                        <?php echo number_format($item['price'] * $item['quantity']); ?>
                                    </div>
                                    <a href="?remove_from_cart=<?php echo $id; ?>" class="cart-remove" onclick="return confirm('حذف شود؟')">🗑️</a>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="cart-total">
                                مجموع: <?php echo number_format($cartTotal); ?> تومان
                            </div>
                            
                            <button type="submit" name="update_cart" class="btn-checkout">🔄 به‌روزرسانی سبد</button>
                        </form>
                        
                        <form method="POST" action="checkout.php">
                            <button type="submit" class="btn-checkout" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); margin-top: 10px;">
                                ✅ ثبت نهایی خرید
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="empty-cart">
                            🛒 سبد خرید شما خالی است
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- محصولات -->
            <div class="products-column">
                <!-- فیلترها -->
                <div class="filter-bar">
                    <h3>🔍 فیلتر محصولات</h3>
                    <form method="GET" action="" class="filter-form">
                        <div class="filter-group">
                            <label>جستجو</label>
                            <input type="text" name="search" placeholder="نام کالا..." value="<?php echo htmlspecialchars($filterSearch); ?>">
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
                            <label>حداقل قیمت</label>
                            <input type="number" name="min_price" placeholder="0" value="<?php echo $filterMinPrice ?: ''; ?>">
                        </div>
                        <div class="filter-group">
                            <label>حداکثر قیمت</label>
                            <input type="number" name="max_price" placeholder="نامحدود" value="<?php echo $filterMaxPrice ?: ''; ?>">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn-filter">اعمال فیلتر</button>
                            <a href="shop.php" class="btn-reset">حذف فیلتر</a>
                        </div>
                    </form>
                </div>
                
                <!-- لیست محصولات -->
                <div class="products-grid">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $stockClass = '';
                            $stockText = 'موجودی: ' . number_format($product['warehouse_stock']);
                            if ($product['warehouse_stock'] <= $product['min_stock_alert']) {
                                $stockClass = 'stock-low';
                                $stockText .= ' (تا اتمام موجودی)';
                            } else {
                                $stockClass = 'stock-normal';
                            }
                            ?>
                            <div class="product-card">
                                <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                                    <img src="<?php echo $product['image']; ?>" class="product-image">
                                <?php elseif (!empty($product['thumbnail']) && file_exists($product['thumbnail'])): ?>
                                    <img src="<?php echo $product['thumbnail']; ?>" class="product-image">
                                <?php else: ?>
                                    <div class="no-image">📦</div>
                                <?php endif; ?>
                                
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($product['merch_name']); ?></div>
                                    <div class="product-price"><?php echo number_format($product['price']); ?> تومان</div>
                                    <div class="product-category">🏷️ <?php echo htmlspecialchars($product['cat_name']); ?></div>
                                    <div class="product-stock <?php echo $stockClass; ?>"><?php echo $stockText; ?></div>
                                    
                                    <form method="GET" action="">
                                        <input type="hidden" name="add_to_cart" value="<?php echo $product['id']; ?>">
                                        <div class="quantity-input">
                                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['warehouse_stock']; ?>">
                                            <button type="submit" class="btn-add-to-cart">🛒 افزودن به سبد</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 60px; background: white; border-radius: 15px;">
                            🚫 محصولی با این مشخصات یافت نشد
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
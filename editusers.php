<?php
session_start();

// بررسی امنیتی - فقط کاربر admin می‌تواند وارد شود
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// چک کردن نام کاربری - فقط admin دسترسی دارد
if ($_SESSION['user_username'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    echo '<!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>دسترسی غیرمجاز</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
            .error-container {
                text-align: center;
                padding: 100px 20px;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .error-box {
                background: white;
                padding: 40px;
                border-radius: 20px;
                max-width: 500px;
            }
            .error-icon {
                font-size: 80px;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc3545;
                margin-bottom: 15px;
            }
            p {
                color: #666;
                margin-bottom: 25px;
            }
            .btn-back {
                background: #667eea;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 8px;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-box">
                <div class="error-icon">⛔</div>
                <h1>دسترسی غیرمجاز</h1>
                <p>شما مجوز دسترسی به این صفحه را ندارید.<br>این صفحه فقط برای مدیر سیستم قابل دسترسی است.</p>
                <a href="main.php" class="btn-back">→ بازگشت به داشبورد</a>
            </div>
        </div>
    </body>
    </html>';
    exit();
}

require_once 'config/database.php';

// متغیرها برای ویرایش و پیام
$edit_user = null;
$message = '';
$message_type = '';

// حذف کاربر
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // جلوگیری از حذف ادمین اصلی
    $stmt = $pdo->prepare("SELECT Username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user && $user['Username'] === 'admin') {
        $message = '❌ شما نمی‌توانید کاربر ادمین اصلی را حذف کنید!';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = '✅ کاربر با موفقیت حذف شد';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = '❌ خطا در حذف کاربر: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// دریافت اطلاعات برای ویرایش
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// اضافه یا ویرایش کاربر
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $position = trim($_POST['position'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id = $_POST['user_id'] ?? '';
    
    // اعتبارسنجی
    if (empty($firstname) || empty($lastname) || empty($username)) {
        $message = '❌ لطفاً نام، نام خانوادگی و نام کاربری را پر کنید';
        $message_type = 'error';
    } else {
        try {
            if ($user_id && $user_id != '') {
                // حالت ویرایش - جلوگیری از تغییر نام کاربری ادمین به چیز دیگر
                $checkAdmin = $pdo->prepare("SELECT Username FROM users WHERE id = ?");
                $checkAdmin->execute([$user_id]);
                $oldUser = $checkAdmin->fetch();
                
                if ($oldUser && $oldUser['Username'] === 'admin' && $username !== 'admin') {
                    $message = '❌ نمی‌توانید نام کاربری ادمین را تغییر دهید!';
                    $message_type = 'error';
                } else {
                    if (empty($password)) {
                        $stmt = $pdo->prepare("UPDATE users SET FirstName=?, LastName=?, Username=?, Position=?, Description=? WHERE id=?");
                        $stmt->execute([$firstname, $lastname, $username, $position, $description, $user_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET FirstName=?, LastName=?, Username=?, Password=?, Position=?, Description=? WHERE id=?");
                        $stmt->execute([$firstname, $lastname, $username, $password, $position, $description, $user_id]);
                    }
                    $message = '✅ اطلاعات کاربر با موفقیت به‌روزرسانی شد';
                    $message_type = 'success';
                    $edit_user = null;
                }
            } else {
                // حالت اضافه کردن جدید
                if (empty($password)) {
                    $message = '❌ لطفاً رمز عبور را وارد کنید';
                    $message_type = 'error';
                } else {
                    $check = $pdo->prepare("SELECT id FROM users WHERE Username = ?");
                    $check->execute([$username]);
                    if ($check->rowCount() > 0) {
                        $message = '❌ این نام کاربری قبلاً وجود دارد';
                        $message_type = 'error';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO users (FirstName, LastName, Username, Password, Position, Description, Date1) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->execute([$firstname, $lastname, $username, $password, $position, $description]);
                        $message = '✅ کاربر جدید با موفقیت اضافه شد';
                        $message_type = 'success';
                    }
                }
            }
        } catch(PDOException $e) {
            $message = '❌ خطا: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// دریافت لیست همه کاربران
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>مدیریت کاربران - فقط ادمین</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .users-management {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-container h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .form-field {
            margin-bottom: 15px;
        }
        
        .form-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-field input, 
        .form-field textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-field textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-field input:focus, 
        .form-field textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            margin-right: 10px;
        }
        
        .users-table {
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
            color: #333;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .btn-edit {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            margin: 0 2px;
            display: inline-block;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            margin: 0 2px;
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
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media screen and (max-width: 768px) {
            .users-management {
                padding: 10px;
            }
            
            th, td {
                padding: 8px;
                font-size: 12px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .badge {
            background: #667eea;
            color: white;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 11px;
            display: inline-block;
        }
        
        .current-user {
            background: #fff3cd;
        }
        
        .admin-badge {
            background: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="users-management">
            <a href="main.php" class="btn-back">← بازگشت به داشبورد</a>
            
            <div style="background: #667eea; color: white; padding: 10px 20px; border-radius: 10px; margin-bottom: 20px;">
                👑 پنل مدیریت کاربران - شما با دسترسی ادمین وارد شده‌اید
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- فرم اضافه/ویرایش کاربر -->
            <div class="form-container">
                <h2><?php echo $edit_user ? '✏️ ویرایش کاربر' : '➕ افزودن کاربر جدید'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id'] ?? ''; ?>">
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label>نام <span style="color:red">*</span></label>
                            <input type="text" name="firstname" required value="<?php echo htmlspecialchars($edit_user['FirstName'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label>نام خانوادگی <span style="color:red">*</span></label>
                            <input type="text" name="lastname" required value="<?php echo htmlspecialchars($edit_user['LastName'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label>نام کاربری <span style="color:red">*</span></label>
                            <input type="text" name="username" required value="<?php echo htmlspecialchars($edit_user['Username'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label>رمز عبور <?php echo $edit_user ? '<span style="color:#888">(در صورت تمایل به تغییر وارد کنید)</span>' : '<span style="color:red">*</span>'; ?></label>
                            <input type="password" name="password" <?php echo $edit_user ? '' : 'required'; ?>>
                        </div>
                        
                        <div class="form-field">
                            <label>سمت</label>
                            <input type="text" name="position" value="<?php echo htmlspecialchars($edit_user['Position'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-field full-width">
                            <label>توضیحات</label>
                            <textarea name="description"><?php echo htmlspecialchars($edit_user['Description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <?php echo $edit_user ? '💾 به‌روزرسانی' : '➕ افزودن کاربر'; ?>
                    </button>
                    
                    <?php if ($edit_user): ?>
                        <a href="editusers.php" class="btn-cancel">❌ انصراف</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- لیست کاربران -->
            <div class="users-table">
                <h2 style="padding: 20px 20px 0 20px;">👥 لیست کاربران (<?php echo count($users); ?> نفر)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>نام</th>
                            <th>نام خانوادگی</th>
                            <th>نام کاربری</th>
                            <th>سمت</th>
                            <th>تاریخ عضویت</th>
                            <th>آخرین ورود</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr <?php echo ($user['id'] == $_SESSION['user_id']) ? 'class="current-user"' : ''; ?>>
                                <td><?php echo $user['id']; 
                                    if ($user['Username'] === 'admin') echo ' <span class="admin-badge">ادمین</span>';
                                ?></td>
                                <td><?php echo htmlspecialchars($user['FirstName']); ?></td>
                                <td><?php echo htmlspecialchars($user['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                <td><?php echo htmlspecialchars($user['Position'] ?: '—'); ?></td>
                                <td><?php echo htmlspecialchars($user['Date1']); ?></td>
                                <td><?php echo htmlspecialchars($user['DateLastLogin'] ?: '—'); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn-edit">✏️ ویرایش</a>
                                    <?php if ($user['Username'] !== 'admin'): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('آیا از حذف کاربر «<?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>» مطمئن هستید؟')">
                                            🗑️ حذف
                                        </a>
                                    <?php else: ?>
                                        <span class="badge">ادمین اصلی</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($users) == 0): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    🚫 هیچ کاربری یافت نشد
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
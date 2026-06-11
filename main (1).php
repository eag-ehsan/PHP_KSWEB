<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// اطلاعات کاربر از سشن
$firstName = $_SESSION['user_firstname'];
$lastName = $_SESSION['user_lastname'];
$position = $_SESSION['user_position'];
$username = $_SESSION['user_username'];
$date1 = $_SESSION['user_date1'];
$description = $_SESSION['user_description'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>داشبورد اصلی</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a472a 0%, #0e2e1a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* هدر دسکتاپ */
        .dashboard-header {
            background: rgba(10, 31, 18, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 217, 102, 0.3);
        }

        /* کارت اطلاعات کاربر */
        .user-info-card {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
        }

        .user-details {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 20px;
            flex: 2;
            min-width: 250px;
        }

        .user-details h3 {
            color: #ffd966;
            margin-bottom: 15px;
            font-size: 1.3rem;
            border-right: 3px solid #ffd966;
            padding-right: 12px;
        }

        .user-details p {
            color: #ffefb9;
            margin: 8px 0;
            font-size: 0.95rem;
            word-break: break-word;
        }

        .user-details strong {
            color: #ffd966;
            display: inline-block;
            min-width: 90px;
        }

        /* ========== دکمه‌ها - حالت دسکتاپ ========== */
        .buttons-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            flex: 3;
            justify-content: flex-end;
            align-items: center;
        }

        .game-btn, .admin-link, .logout-btn {
            background: rgba(244, 197, 66, 0.15);
            backdrop-filter: blur(5px);
            color: #ffefb9;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            font-size: 0.9rem;
            border: 1px solid rgba(244, 197, 66, 0.3);
            cursor: pointer;
        }

        .game-btn:hover, .admin-link:hover {
            background: #f4c542;
            color: #2d2b1f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .logout-btn {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.5);
        }

        .logout-btn:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
        }

        /* محتوای اصلی */
        .dashboard-content {
            background: rgba(10, 31, 18, 0.8);
            backdrop-filter: blur(5px);
            border-radius: 24px;
            padding: 50px 20px;
            text-align: center;
            border: 1px solid rgba(255, 217, 102, 0.2);
        }

        .under-construction .construction-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .under-construction h1 {
            color: #ffd966;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .under-construction p {
            color: #ffefb9;
            font-size: 1rem;
        }

        /* ========== حالت موبایل ========== */
        @media screen and (max-width: 768px) {
            body {
                padding: 15px;
            }

            .dashboard-header {
                padding: 20px 15px;
            }

            .user-info-card {
                flex-direction: column;
            }

            .user-details {
                width: 100%;
                padding: 15px;
                margin-bottom: 10px;
            }

            .user-details p {
                font-size: 0.85rem;
            }

            .user-details strong {
                min-width: 80px;
            }

            /* دکمه‌ها در موبایل - ستونی و هم اندازه */
            .buttons-container {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            .game-btn, .admin-link, .logout-btn {
                width: 100%;
                justify-content: center;
                padding: 14px 16px;
                font-size: 1rem;
                text-align: center;
            }

            .dashboard-content {
                padding: 30px 15px;
            }

            .under-construction h1 {
                font-size: 1.5rem;
            }
        }

        /* ========== حالت تبلت ========== */
        @media screen and (min-width: 769px) and (max-width: 1024px) {
            .buttons-container {
                gap: 8px;
            }
            
            .game-btn, .admin-link, .logout-btn {
                padding: 10px 14px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="user-info-card">
                <!-- اطلاعات کاربر - در موبایل پایین می‌آید -->
                <div class="user-details">
                    <h3>👤 اطلاعات کاربر</h3>
                    <p><strong>نام:</strong> <?php echo htmlspecialchars($firstName); ?></p>
                    <p><strong>نام خانوادگی:</strong> <?php echo htmlspecialchars($lastName); ?></p>
                    <p><strong>نام کاربری:</strong> <?php echo htmlspecialchars($username); ?></p>
                    <p><strong>سمت:</strong> <?php echo htmlspecialchars($position); ?></p>
                    <p><strong>تاریخ عضویت:</strong> <?php echo htmlspecialchars($date1); ?></p>
                    <p><strong>توضیحات:</strong> <?php echo htmlspecialchars($description); ?></p>
                </div>
                
                <!-- دکمه‌ها - در موبایل ستونی -->
                <div class="buttons-container">
                    <?php if ($username === 'admin'): ?>
                        <a href="editusers.php" class="admin-link">👥 مدیریت کاربران</a>
                    <?php endif; ?>
                    <a href="products.php" class="admin-link">🛒 ثبت خریدها</a>
                    <a href="ball8.html" class="game-btn">🎮 بازی پینگ پنگ</a>
                    <a href="mario.html" class="game-btn">🎮 بازی ماریو </a>
                    <a href="guitar2.html" class="game-btn">   گیتار و آکوردها  </a>
                    <a href="guitar3.php" class="game-btn">   گیتار و آکوردها  </a>
                    
                    
                    
                    
                    
                    <a href="level_editor.html" class="game-btn">🛠️ ویرایشگر سطح</a>
                    <a href="encryption.php" class="admin-link">🔐 کدگذاری تصاویر</a>
                    <a href="e_edit.php" class="admin-link">📦 مدیریت کالاها</a>
                    <a href="shop.php" class="admin-link">🛍️ فروشگاه</a>
                    <a href="logout.php" class="logout-btn">🚪 خروج از سیستم</a>
                </div>
            </div>
        </header>
        
        <main class="dashboard-content">
            <div class="under-construction">
                <div class="construction-icon">🚧</div>
                <h1>در دست ساخت</h1>
                <p>این بخش به زودی تکمیل خواهد شد</p>
            </div>
        </main>
    </div>
</body>
</html>
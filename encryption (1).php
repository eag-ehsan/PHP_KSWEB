<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// بررسی وجود فولدر exfiles
$exfiles_dir = 'exfiles';
if (!file_exists($exfiles_dir)) {
    mkdir($exfiles_dir, 0777, true);
}

// بررسی وجود فایل کلید
if (!file_exists('encryption_key.php')) {
    die('❌ فایل encryption_key.php وجود ندارد!');
}

// خواندن کلید از فایل
$keyData = include 'encryption_key.php';

// بررسی صحت کلید
if (!isset($keyData['key']) || !isset($keyData['iv']) || strlen($keyData['key']) < 16) {
    die('❌ ساختار فایل کلید صحیح نیست!');
}

$message = '';
$messageType = '';
$decryptedFiles = [];

// تابع امن برای دیکود کردن با مدیریت خطا
function safeDecrypt($encryptedData, $key, $iv) {
    try {
        $decrypted = openssl_decrypt(
            $encryptedData,
            'AES-128-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($decrypted === false) {
            return false;
        }
        
        return $decrypted;
    } catch (Exception $e) {
        return false;
    }
}

// بخش 1: کدگذاری تصویر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'encrypt') {
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($_FILES['image_file']['size'] <= 5 * 1024 * 1024) {
                $imageData = file_get_contents($_FILES['image_file']['tmp_name']);
                
                // رمزگذاری با کلید فعلی
                $encrypted = openssl_encrypt(
                    $imageData,
                    'AES-128-CBC',
                    $keyData['key'],
                    OPENSSL_RAW_DATA,
                    $keyData['iv']
                );
                
                if ($encrypted === false) {
                    $message = '❌ خطا در رمزگذاری فایل';
                    $messageType = 'error';
                } else {
                    $new_filename = time() . '_' . uniqid() . '.eag';
                    $save_path = $exfiles_dir . '/' . $new_filename;
                    
                    $info = [
                        'original_name' => $filename,
                        'mime_type' => $_FILES['image_file']['type'],
                        'size' => $_FILES['image_file']['size'],
                        'date' => date('Y-m-d H:i:s')
                    ];
                    
                    file_put_contents($save_path, $encrypted);
                    $info_path = $exfiles_dir . '/' . $new_filename . '.json';
                    file_put_contents($info_path, json_encode($info));
                    
                    $message = '✅ تصویر با موفقیت کدگذاری شد!';
                    $messageType = 'success';
                }
            } else {
                $message = '❌ حجم فایل بیشتر از 5 مگابایت است';
                $messageType = 'error';
            }
        } else {
            $message = '❌ فرمت فایل پشتیبانی نمی‌شود (jpg, png, gif, webp)';
            $messageType = 'error';
        }
    } else {
        $message = '❌ لطفاً یک فایل تصویری انتخاب کنید';
        $messageType = 'error';
    }
}

// بخش 2: دریافت لیست فایل‌های کدگذاری شده
$encrypted_files = glob($exfiles_dir . '/*.eag');
foreach ($encrypted_files as $enc_file) {
    $info_path = $enc_file . '.json';
    $info = [];
    if (file_exists($info_path)) {
        $info = json_decode(file_get_contents($info_path), true);
    } else {
        $info = [
            'original_name' => basename($enc_file, '.eag'),
            'mime_type' => 'image/jpeg',
            'size' => filesize($enc_file),
            'date' => date('Y-m-d H:i:s', filemtime($enc_file))
        ];
    }
    
    $decryptedFiles[] = [
        'path' => $enc_file,
        'info' => $info
    ];
}

// دیکود کردن و نمایش تصویر برای تامبنیل (با مدیریت خطا)
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $view_file = $exfiles_dir . '/' . basename($_GET['view']);
    if (file_exists($view_file) && pathinfo($view_file, PATHINFO_EXTENSION) == 'eag') {
        $encryptedData = file_get_contents($view_file);
        $decrypted = safeDecrypt($encryptedData, $keyData['key'], $keyData['iv']);
        
        if ($decrypted === false) {
            // نمایش تصویر خطا
            $error_image = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNmZjAwMDAiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1zaXplPSIxNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj7YpdmI2KfbjNqpINin24zYsdin2YY8L3RleHQ+PC9zdmc+';
            header("Content-Type: image/svg+xml");
            echo base64_decode(str_replace('data:image/svg+xml;base64,', '', $error_image));
            exit();
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $decrypted);
        finfo_close($finfo);
        
        header("Content-Type: $mimeType");
        header("Content-Length: " . strlen($decrypted));
        echo $decrypted;
        exit();
    }
}

// دانلود فایل اصلی (export) با مدیریت خطا
if (isset($_GET['export']) && !empty($_GET['export'])) {
    $export_file = $exfiles_dir . '/' . basename($_GET['export']);
    if (file_exists($export_file) && pathinfo($export_file, PATHINFO_EXTENSION) == 'eag') {
        $encryptedData = file_get_contents($export_file);
        $decrypted = safeDecrypt($encryptedData, $keyData['key'], $keyData['iv']);
        
        if ($decrypted === false) {
            die('❌ خطا: کلید رمزگذاری مطابقت ندارد! فایل قابل دیکود نیست.');
        }
        
        $info_path = $export_file . '.json';
        $original_name = 'decrypted_image.jpg';
        if (file_exists($info_path)) {
            $info = json_decode(file_get_contents($info_path), true);
            $original_name = $info['original_name'];
        }
        
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $original_name . "\"");
        header("Content-Length: " . strlen($decrypted));
        echo $decrypted;
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>کدگذاری و دیکود تصاویر</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .encryption-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .upload-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
        }
        .file-input-wrapper input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            display: inline-block;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .file-label:hover {
            transform: translateY(-2px);
        }
        .btn-encrypt {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-encrypt:hover {
            background: #218838;
        }
        .gallery-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .gallery-item {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
            cursor: pointer;
            position: relative;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #ddd;
        }
        .thumbnail-error {
            width: 100%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8d7da;
            color: #721c24;
            font-size: 12px;
            text-align: center;
        }
        .file-info {
            padding: 10px;
            font-size: 12px;
        }
        .file-name {
            font-weight: bold;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-size {
            color: #666;
            font-size: 10px;
        }
        .error-badge {
            background: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            display: inline-block;
            margin-top: 5px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            overflow: auto;
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            margin-top: 5%;
        }
        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #bbb;
        }
        .modal-buttons {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
        }
        .btn-export {
            background: #17a2b8;
            color: white;
            padding: 10px 25px;
            text-decoration: none;
            border-radius: 8px;
            margin: 0 10px;
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
        .empty-gallery {
            text-align: center;
            padding: 60px;
            color: #888;
        }
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
            .thumbnail {
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="encryption-container">
        <a href="main.php" class="btn-back">← بازگشت به داشبورد</a>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- بخش 1: کدگذاری تصویر -->
        <div class="upload-section">
            <h2>🔐 کدگذاری تصویر جدید</h2>
            <form method="POST" action="" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="action" value="encrypt">
                <div class="file-input-wrapper">
                    <input type="file" name="image_file" id="image_file" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <label for="image_file" class="file-label">📁 انتخاب تصویر</label>
                    <span id="selected_file" style="margin-right: 15px; color: #666;">هیچ فایلی انتخاب نشده</span>
                </div>
                <div>
                    <button type="submit" class="btn-encrypt">🔒 کدگذاری و ذخیره</button>
                </div>
            </form>
            <small style="color: #888; display: block; margin-top: 15px;">
                فرمت‌های مجاز: JPG, PNG, GIF, WEBP | حداکثر حجم: 5 مگابایت
            </small>
        </div>
        
        <!-- بخش 2: نمایش فایل‌های کدگذاری شده -->
        <div class="gallery-section">
            <h2>📂 فایل‌های کدگذاری شده (<?php echo count($decryptedFiles); ?> فایل)</h2>
            
            <?php if (count($decryptedFiles) > 0): ?>
                <div class="gallery-grid">
                    <?php foreach ($decryptedFiles as $file): ?>
                        <?php 
                        $fileId = basename($file['path']);
                        $fileName = $file['info']['original_name'];
                        $fileSize = round($file['info']['size'] / 1024, 2);
                        $fileDate = $file['info']['date'];
                        ?>
                        <div class="gallery-item" onclick="showImage('<?php echo $fileId; ?>')">
                            <img src="encryption.php?view=<?php echo urlencode($fileId); ?>" 
                                 class="thumbnail" 
                                 alt="<?php echo htmlspecialchars($fileName); ?>"
                                 onerror="this.onerror=null; this.parentElement.querySelector('.thumbnail-error')?.style.display='flex'; this.style.display='none';">
                            <div class="thumbnail-error" style="display: none; align-items: center; justify-content: center; background: #f8d7da; color: #721c24; height: 150px;">
                                🔑 خطا در دیکود<br>کلید مطابقت ندارد
                            </div>
                            <div class="file-info">
                                <div class="file-name" title="<?php echo htmlspecialchars($fileName); ?>">
                                    <?php echo htmlspecialchars(mb_substr($fileName, 0, 20)) . (mb_strlen($fileName) > 20 ? '...' : ''); ?>
                                </div>
                                <div class="file-size">📁 <?php echo $fileSize; ?> KB</div>
                                <div class="file-size">📅 <?php echo $fileDate; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-gallery">
                    <p>🔐 هنوز هیچ فایل کدگذاری شده‌ای وجود ندارد</p>
                    <p>از قسمت بالا یک تصویر انتخاب کنید و کدگذاری کنید</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal برای نمایش تصویر بزرگ -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
        <div class="modal-buttons">
            <a href="#" id="exportBtn" class="btn-export">💾 دانلود فایل اصلی</a>
        </div>
    </div>
    
    <script>
        document.getElementById('image_file').addEventListener('change', function(e) {
            var fileName = e.target.files[0]?.name || 'هیچ فایلی انتخاب نشده';
            document.getElementById('selected_file').textContent = fileName;
        });
        
        function showImage(fileId) {
            var modal = document.getElementById('imageModal');
            var modalImg = document.getElementById('modalImage');
            var exportBtn = document.getElementById('exportBtn');
            
            modal.style.display = 'block';
            modalImg.src = 'encryption.php?view=' + encodeURIComponent(fileId);
            exportBtn.href = 'encryption.php?export=' + encodeURIComponent(fileId);
            
            // اگر تصویر خطا داشت
            modalImg.onerror = function() {
                modalImg.style.display = 'none';
                alert('❌ خطا: کلید رمزگذاری مطابقت ندارد! این فایل با کلید دیگری کدگذاری شده است.');
            };
        }
        
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            var modal = document.getElementById('imageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
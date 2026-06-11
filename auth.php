<?php
// این فایل در تمام صفحات php به جز لاگین باید include شود
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// بروزرسانی آخرین فعالیت (اختیاری)
$_SESSION['last_activity'] = time();
?>
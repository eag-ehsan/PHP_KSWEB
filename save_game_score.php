<?php
// ==============================================
// save_game_score.php
// ذخیره رکورد بازی (بدون FOREIGN KEY)
// ==============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// تنظیمات دیتابیس
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'company_db';

// اتصال به دیتابیس
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'خطا در اتصال به دیتابیس']);
    exit;
}

// دریافت داده از درخواست POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'داده ارسال نشده است']);
    exit;
}

// اعتبارسنجی داده‌ها
$required = ['user_id', 'game_name', 'level', 'score'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success' => false, 'error' => "فیلد {$field} الزامی است"]);
        exit;
    }
}

$user_id = intval($input['user_id']);
$game_name = $conn->real_escape_string($input['game_name']);
$level = intval($input['level']);
$score = floatval($input['score']);
$score_type = isset($input['score_type']) ? $conn->real_escape_string($input['score_type']) : 'time';
$session_ip = $_SERVER['REMOTE_ADDR'] ?? null;

// بررسی وجود کاربر
$check_user = $conn->query("SELECT id, Username FROM users WHERE id = {$user_id}");
if ($check_user->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'کاربر یافت نشد']);
    exit;
}
$user = $check_user->fetch_assoc();

// ذخیره رکورد جدید
$sql = "INSERT INTO game_scores (user_id, game_name, level, score, score_type, session_ip) 
        VALUES ({$user_id}, '{$game_name}', {$level}, {$score}, '{$score_type}', " . ($session_ip ? "'{$session_ip}'" : "NULL") . ")";

if ($conn->query($sql)) {
    // به‌روزرسانی جدول high_scores (بهترین رکورد)
    // ابتدا بهترین رکورد فعلی را پیدا کن
    $best = $conn->query("SELECT MIN(score) as best FROM game_scores WHERE user_id = {$user_id} AND game_name = '{$game_name}' AND level = {$level}");
    $best_row = $best->fetch_assoc();
    $current_best = floatval($best_row['best']);
    $is_best = ($score <= $current_best);
    
    // اگر رکورد جدید بهترین است، high_scores را به‌روز کن
    if ($is_best) {
        $conn->query("
            INSERT INTO high_scores (user_id, username, game_name, level, best_score, score_type, achieved_at)
            VALUES ({$user_id}, '{$user['Username']}', '{$game_name}', {$level}, {$score}, '{$score_type}', NOW())
            ON DUPLICATE KEY UPDATE
                best_score = {$score},
                username = '{$user['Username']}',
                score_type = '{$score_type}',
                achieved_at = NOW()
        ");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'رکورد با موفقیت ذخیره شد',
        'is_best' => $is_best,
        'best_score' => $current_best,
        'username' => $user['Username']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
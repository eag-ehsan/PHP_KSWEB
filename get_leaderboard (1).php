<?php
// ==============================================
// get_leaderboard.php
// دریافت جدول امتیازات برای یک لول خاص
// ==============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'company_db';

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'خطا در اتصال به دیتابیس']);
    exit;
}

// دریافت بهترین رکوردها برای لول مورد نظر
$sql = "SELECT h.user_id, h.username, h.best_score, h.achieved_at, h.score_type
        FROM high_scores h
        WHERE h.game_name = 'ball_gravity' AND h.level = {$level}
        ORDER BY h.best_score ASC
        LIMIT {$limit}";

$result = $conn->query($sql);

$leaderboard = [];
$rank = 1;
while($row = $result->fetch_assoc()) {
    $leaderboard[] = [
        'rank' => $rank++,
        'user_id' => $row['user_id'],
        'username' => $row['username'],
        'score' => floatval($row['best_score']),
        'achieved_at' => $row['achieved_at']
    ];
}

// همچنین رکورد شخصی کاربر درخواستی (اگر user_id ارسال شده باشد)
$personal_best = null;
if (isset($_GET['user_id'])) {
    $uid = intval($_GET['user_id']);
    $personal = $conn->query("SELECT best_score FROM high_scores WHERE user_id = {$uid} AND game_name = 'ball_gravity' AND level = {$level}");
    if ($personal->num_rows > 0) {
        $personal_best = floatval($personal->fetch_assoc()['best_score']);
    }
}

echo json_encode([
    'success' => true,
    'level' => $level,
    'leaderboard' => $leaderboard,
    'personal_best' => $personal_best
]);

$conn->close();
?>
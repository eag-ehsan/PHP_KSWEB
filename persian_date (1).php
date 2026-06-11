<?php
function gregorian_to_jalali($g_y, $g_m, $g_d)
{
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    
    // کبیسه گیری میلادی
    if ($g_y % 4 == 0 && ($g_y % 100 != 0 || $g_y % 400 == 0)) {
        $g_days_in_month[1] = 29;
    }
    
    // محاسبه روزهای گذشته از سال میلادی
    $days_passed = 0;
    for ($i = 0; $i < ($g_m - 1); $i++) {
        $days_passed += $g_days_in_month[$i];
    }
    $days_passed += $g_d - 1;
    
    // محاسبه سال شمسی
    $jy = $g_y - 621;
    $days_in_year = 365;
    if ($jy % 4 == 0) $days_in_year = 366;
    
    $days_passed -= 79; // تفاوت ابتدای سال
    
    while ($days_passed >= $days_in_year) {
        $days_passed -= $days_in_year;
        $jy++;
        $days_in_year = ($jy % 4 == 0) ? 366 : 365;
    }
    
    while ($days_passed < 0) {
        $jy--;
        $days_in_year = ($jy % 4 == 0) ? 366 : 365;
        $days_passed += $days_in_year;
    }
    
    // محاسبه ماه و روز شمسی
    $jm = 1;
    for ($i = 0; $i < 12; $i++) {
        if ($days_passed < $j_days_in_month[$i]) break;
        $days_passed -= $j_days_in_month[$i];
        $jm++;
    }
    
    $jd = $days_passed + 1;
    
    return array($jy, $jm, $jd);
}

function jdate_simple($format, $date_string = null) {
    // اگر تاریخ داده نشده، از تاریخ امروز استفاده کن
    if ($date_string === null) {
        $date_string = date('Y-m-d');
    }
    
    // اگر فرمت شامل زمان هم هست
    $include_time = (strpos($format, 'H') !== false || strpos($format, 'i') !== false || strpos($format, 's') !== false);
    
    $timestamp = strtotime($date_string);
    $year = date('Y', $timestamp);
    $month = date('m', $timestamp);
    $day = date('d', $timestamp);
    
    // اگر زمان هم نیاز است
    $hour = date('H', $timestamp);
    $minute = date('i', $timestamp);
    $second = date('s', $timestamp);
    
    list($jy, $jm, $jd) = gregorian_to_jalali($year, $month, $day);
    
    // آماده سازی جایگزین‌ها
    $result = $format;
    
    // جایگزینی تاریخ
    $result = str_replace('Y', $jy, $result);
    $result = str_replace('m', sprintf("%02d", $jm), $result);
    $result = str_replace('d', sprintf("%02d", $jd), $result);
    
    // جایگزینی زمان
    if ($include_time) {
        $result = str_replace('H', sprintf("%02d", $hour), $result);
        $result = str_replace('i', sprintf("%02d", $minute), $result);
        $result = str_replace('s', sprintf("%02d", $second), $result);
    }
    
    return $result;
}

// تابع کمکی برای تبدیل یک تاریخ به شمسی
function convert_to_shamsi($miladi_date) {
    return jdate_simple('Y/m/d', $miladi_date);
}
?>
<?php
/*------------------------------------------------------------------*/
/*  Jalali Date & Time Functions - Version 2.0                      */
/*  Author: Sallar Kaboli - http://sallar.me                        */
/*------------------------------------------------------------------*/

function jdate($format, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    // تبدیل timestamp به تاریخ میلادی
    $date = getdate($timestamp);
    $g Year = $date['year'];
    $gMonth = $date['mon'];
    $gDay = $date['mday'];
    
    // محاسبه تاریخ شمسی
    $gDaysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    
    // کبیسه گیری میلادی
    if (($gYear % 4 == 0) && ($gYear % 100 != 0) || ($gYear % 400 == 0)) {
        $gDaysInMonth[1] = 29;
    }
    
    // محاسبه روز ژولینی
    $gDays = 0;
    for ($i = 0; $i < $gMonth - 1; $i++) {
        $gDays += $gDaysInMonth[$i];
    }
    $gDays += $gDay;
    
    // محاسبه سال شمسی
    $jYear = $gYear - 621;
    $jDays = $gDays + 79;
    
    if ($jDays > 365) {
        $jYear++;
        $jDays -= 365;
        if (($jYear % 4 == 0) && ($jDays > 366)) {
            $jDays -= 366;
        } elseif (($jYear % 4 != 0) && ($jDays > 365)) {
            $jDays -= 365;
        }
    }
    
    // محاسبه ماه و روز شمسی
    $jMonthsDays = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    if ($jYear % 4 != 0) {
        $jMonthsDays[11] = 29;
    }
    
    $jMonth = 1;
    for ($i = 0; $i < 12; $i++) {
        if ($jDays > $jMonthsDays[$i]) {
            $jDays -= $jMonthsDays[$i];
            $jMonth++;
        } else {
            break;
        }
    }
    
    $jDay = $jDays;
    
    // نام ماه‌ها و روزها
    $monthNames = array(
        1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    );
    
    $weekDays = array(
        0 => 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه',
        'پنجشنبه', 'جمعه', 'شنبه'
    );
    
    // فرمت دهی خروجی
    $result = '';
    $len = strlen($format);
    for ($i = 0; $i < $len; $i++) {
        $char = $format[$i];
        if ($char == '\\') {
            $i++;
            $result .= $format[$i];
            continue;
        }
        
        switch ($char) {
            case 'd':
                $result .= sprintf("%02d", $jDay);
                break;
            case 'j':
                $result .= $jDay;
                break;
            case 'm':
                $result .= sprintf("%02d", $jMonth);
                break;
            case 'n':
                $result .= $jMonth;
                break;
            case 'Y':
                $result .= $jYear;
                break;
            case 'y':
                $result .= sprintf("%02d", $jYear % 100);
                break;
            case 'F':
                $result .= $monthNames[$jMonth];
                break;
            case 'M':
                $result .= mb_substr($monthNames[$jMonth], 0, 3);
                break;
            case 'H':
                $result .= sprintf("%02d", $date['hours']);
                break;
            case 'i':
                $result .= sprintf("%02d", $date['minutes']);
                break;
            case 's':
                $result .= sprintf("%02d", $date['seconds']);
                break;
            case 'G':
                $result .= $date['hours'];
                break;
            case 'l':
                $result .= $weekDays[$date['wday']];
                break;
            case 'w':
                $result .= $date['wday'];
                break;
            default:
                $result .= $char;
        }
    }
    
    return $result;
}
?>
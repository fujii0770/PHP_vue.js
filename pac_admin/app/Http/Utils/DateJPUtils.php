<?php

namespace App\Http\Utils;

/**
 * 日付変換
 * Class DateJPUtils
 * @package App\Http\Utils
 */
class DateJPUtils
{
    const YEAR_JP = [
        'R' => [2019, 5, 1],
        'H' => [1989,1,7]
    ];

    const STATE_DELETE = 9;
    const STATE_VALID = 1;

    public static function convert($dateIn, $format) {
        if(\is_string($dateIn)) $dateIn = new \DateTime($dateIn);
        if(\strpos($format,"g") === 0){
            $year = $dateIn->format('Y');
            $month = $dateIn->format('m');
            $date = $dateIn->format('d');

            $found = false;
            foreach(DateJPUtils::YEAR_JP as $c => $y){
                if($year > $y[0] OR ($year == $y[0] AND ($month > $y[1] OR ($month == $y[1] AND $date >= $y[2])))){ 
                    $found = true; 
                    break;
                }
            }
            if($found){
                $year = $year - $y[0] + 1;
                if($format == "gy.m.d"){
                    return "$c$year.$month.$date";
                }else if($format == "gy/m/d"){
                    return "$c$year/$month/$date";
                }
            }
            $format = \str_replace("g","", $format);
        }
        return $dateIn->format($format);
    }

     
}
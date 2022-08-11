<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeCard extends Model
{
    public $table = 'time_card';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $appends = ['created_date', 'punched_date'];

    protected $casts = [
        'punch_data' => 'array',
        'punched_at' => 'datetime'
    ];

    const PUNCH_DATA = [
        'start1' => null,
        'end1' => null,
        'start2' => null,
        'end2' => null,
        'start3' => null,
        'end3' => null,
        'start4' => null,
        'end4' => null,
        'start5' => null,
        'end5' => null,
    ];

    const WEEK = ['日', '月', '火', '水', '木', '金', '土'];

    public $fillable = [
        'mst_user_id',
        'punch_data',
        'num_flg',
        'updated_at',
        'created_at',
        'punched_at'
    ];


    public function punchDataInit($num)
    {
        if ($this->punch_data) {
            $punchData = $this->punch_data;
        } else {
            $punchData = self::PUNCH_DATA;
        }
        switch ($num) {
            case 1;
                $punchData['start1'] = now()->format('H:i:s');
                break;
            case 2;
                $punchData['end1'] = now()->format('H:i:s');
                break;
            case 3;
                $punchData['start2'] = now()->format('H:i:s');
                break;
            case 4;
                $punchData['end2'] = now()->format('H:i:s');
                break;
            case 5;
                $punchData['start3'] = now()->format('H:i:s');
                break;
            case 6;
                $punchData['end3'] = now()->format('H:i:s');
                break;
            case 7;
                $punchData['start4'] = now()->format('H:i:s');
                break;
            case 8;
                $punchData['end4'] = now()->format('H:i:s');
                break;
            case 9;
                $punchData['start5'] = now()->format('H:i:s');
                break;
            case 10;
                $punchData['end5'] = now()->format('H:i:s');
                break;
        }

        return $punchData;
    }

    public function lastPunched()
    {
        // レコーダーなし
        if (!$this->id) {
            return false;
        } else {
            $punchData = $this->punch_data;
            $last = '';
            switch ($this->num_flg) {
                case 1;
                    $last = $punchData['start1'];
                    break;
                case 2;
                    $last = $punchData['end1'];
                    break;
                case 3;
                    $last = $punchData['start2'];
                    break;
                case 4;
                    $last = $punchData['end2'];
                    break;
                case 5;
                    $last = $punchData['start3'];
                    break;
                case 6;
                    $last = $punchData['end3'];
                    break;
                case 7;
                    $last = $punchData['start4'];
                    break;
                case 8;
                    $last = $punchData['end4'];
                    break;
                case 9;
                    $last = $punchData['start5'];
                    break;
                case 10;
                    $last = $punchData['end5'];
            }

            if ($last) {
                $arr = [
                    'last' => $last,
                    'format_date' => Carbon::parse($this->punched_at)->format('Y/m/d'),
                    'format_time' => Carbon::parse($last)->format('H:i:s'),
                    'format_week' => self::WEEK[Carbon::parse($this->punched_at)->dayOfWeek],
                ];
            } else {
                // レコーダー存在するが、打刻履歴削除された場合
                $arr = false;
            }

            return $arr;
        }
    }

    public function getLastPunchedTypeAttribute()
    {
        if ($this->num_flg) {
            return $this->num_flg % 2 == 1 ? 1 : 2;
        } else {
            return -1;
        }
    }

    public function getEveryPunchedTime()
    {
        $arr = $this->punch_data;
        foreach ($arr as &$item) {
            if ($item) {
                $item = Carbon::parse($item)->format('H:i');
            }
        }

        return $arr;
    }

    public function getCreatedDateAttribute()
    {
        if ($this->id) {
            return Carbon::parse($this->created_at)->format('Y/m/d');
        } else {
            return '';
        }
    }

    public function getPunchedDateAttribute()
    {
        if ($this->id) {
            return Carbon::parse($this->punched_at)->format('Y/m/d');
        } else {
            return '';
        }
    }

    static public function getNumForEdit($data)
    {
        $num_flg = 0;
        if ($data['start1']) {
            $num_flg = 1;
        }
        if ($data['end1']) {
            $num_flg = 2;
        }
        if ($data['start2']) {
            $num_flg = 3;
        }
        if ($data['end2']) {
            $num_flg = 4;
        }
        if ($data['start3']) {
            $num_flg = 5;
        }
        if ($data['end3']) {
            $num_flg = 6;
        }
        if ($data['start4']) {
            $num_flg = 7;
        }
        if ($data['end4']) {
            $num_flg = 8;
        }
        if ($data['start5']) {
            $num_flg = 9;
        }
        if ($data['end5']) {
            $num_flg = 10;
        }

        return $num_flg;
    }
}

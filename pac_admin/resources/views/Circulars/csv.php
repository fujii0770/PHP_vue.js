<?php

$Filename = 'circulars.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $Filename . '');
$output = fopen('php://output', 'w');
fwrite($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
$row = [
    '申請者',
    '回覧ルート',
    '件名',
    'ファイル名',
    '申請日時',
    $status != \App\Http\Utils\CircularUtils::CIRCULAR_COMPLETED_STATUS ? '最終更新日' : '完了日時',
    '回覧状態',
];

fputcsv($output, $row);
foreach ($itemsCircular as $item) {
    if (strpos($item->user_names, '&lt;') !== false || strpos($item->user_names, '&gt;') !== false) {
        $item->user_names = \App\Http\Utils\CommonUtils::replaceCharacter($item->user_names);
    }
    $item->user_names = str_replace(',',PHP_EOL,$item->user_names);
    $item->file_names = str_replace(', ',PHP_EOL,$item->file_names);
    $item->status_name = \App\Http\Utils\AppUtils::CIRCULAR_STATUS[$item->circular_status];
    
    $row = [
        $item->user_name . '<' . $item->user_email . '>',
        $item->user_names,
        $item->title,
        $item->file_names,
        date("Y/m/d H:i:s", strtotime($item->applied_date)),
        $status != \App\Http\Utils\CircularUtils::CIRCULAR_COMPLETED_STATUS ? date("Y/m/d H:i:s", strtotime($item->final_updated_date)) : date("Y/m/d H:i:s", strtotime($item->completed_date)),
        $item->status_name,
    ];
    
    fputcsv($output, $row);
}
fclose($output);                
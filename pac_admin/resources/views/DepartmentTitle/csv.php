<?php
$Filename ='position.csv';
header('Content-Type: text/csv; charset=SJIS');
header('Content-Disposition: attachment; filename='.$Filename.'');
$output = fopen('php://output', 'w');
fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

foreach ($itemsPositionCsv as $item){
    $row = [
        $item->position_name,
        ];
    fputcsv($output, $row);
}
fclose($output);                
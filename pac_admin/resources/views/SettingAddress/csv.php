<?php
$Filename ='address.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$Filename.'');
$output = fopen('php://output', 'w');;
fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
foreach ($query as $query){
    $arr = explode(' ' , $query->name);
    if (count($arr)>1) {
        $row = [
            $query->email,
            $arr[0],
            $arr[1],
            $query->company_name,
            $query->position_name,
        ];
    }else{
        $row = [
            $query->email,
            $query->name,
            '',
            $query->company_name,
            $query->position_name,
        ];
    }

    fputcsv($output, $row);
}
fclose($output);                
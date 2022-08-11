<?php

if ($type == 'user'){
    $Filename ='userlog.csv';
}else{
    $Filename ='adminlog.csv';
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$Filename.'');
$output = fopen('php://output', 'w');
fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
if ($type == 'user'){
    foreach ($arrHistory as $item){
        $row = [
            $item->create_at,
            $item->email ,
            $item->user_name,
            $item->department_name,
            $item->position_name,
            $arrOperation_info[$item->mst_operation_id],
            $item->ip_address,
        ];
        fputcsv($output, $row);
    }
}else{
    foreach ($arrHistory as $item){
        $row = [
            $item->create_at,
            $item->email ,
            $item->user_name,
            $item->department_name,
            $arrOperation_info[$item->mst_operation_id],
        ];
        fputcsv($output, $row);
    }
}
fclose($output);                
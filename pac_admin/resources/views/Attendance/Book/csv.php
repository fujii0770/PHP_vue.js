<?php

$Filename = 'users.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $Filename . '');
$output = fopen('php://output', 'w');
fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
foreach ($users as $user) {
    $department = isset($listDepartmentDetail[$user->info->mst_department_id]) ? $listDepartmentDetail[$user->info->mst_department_id]['text'] : '';
    $position_id = $user->info->mst_position_id;
    $row = [$user->email, $user->family_name, $user->given_name, $department,
        isset($listPosition[$position_id]) ? $listPosition[$position_id]['text'] : "",
        $user->info->postal_code, $user->info->address,
        $user->info->phone_number, $user->info->fax_number,
        '',// ホームページ
        0, '',
        $user->state_flg,
        $user->info->date_stamp_config,
        $user->info->api_apps,

    ];

    // 多要素認証の設定 PAC_5-1686 多要素認証の場合出力項目を増やす
    if ($company->mfa_flg) {
        $row[] = $user->info->mfa_type;
        $row[] = $user->info->email_auth_dest_flg;
        $row[] = $user->info->auth_email;
    }

    $row[] = $user->password == "" ? "未設定" : "設定済";

    fputcsv($output, $row);
}
fclose($output);                
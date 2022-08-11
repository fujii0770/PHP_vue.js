<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Password Reset Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines are the default lines which match reasons
	| that are given by the password broker for a password update attempt
	| has failed, such as for an invalid token or invalid new password.
	|
	*/

    'prefix' => [
        'user' => '[Shachihata Cloud] ',
        'admin' => '[Shachihata Cloud：管理者] '
    ],

    'SendDownloadReserveCompletedMail' => '
        ダウンロードファイルの準備が完了しました。\r\n
        ダウンロード期限内にファイルのダウンロードをお願い致します。\r\n\r\n
        ファイル名：:file_name \r\n
        ダウンロード期限：:dl_period',
];

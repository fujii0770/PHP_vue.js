<?php

namespace App\Utils;


class UploadFileUtils
{
    /* アップロードが許可されているファイルの種類 */
    const FILE_TYPES = [
    'application/pdf',                                                              // pdf
        'application/vnd.ms-excel',                                                 // xls
        'application/msword',                                                       // doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // docx
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',        // xlsx
    ];
}
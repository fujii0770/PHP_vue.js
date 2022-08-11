<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Constraint extends Model
{
    protected $table = 'mst_constraints';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id',
        'max_requests',
        'max_document_size',
        //'user_storage_size',
        'use_storage_percent',
        'max_keep_days',
        'delete_informed_days_ago',
        'long_term_storage_percent',
        'dl_max_keep_days',
        'dl_after_proc',
        'dl_after_keep_days',
        'dl_request_limit',
        'dl_request_limit_per_one_hour',
        'dl_file_total_size_limit',
        'max_ip_address_count',
        'max_viwer_count',
        'sanitize_request_limit',
        'create_user',
        'update_user',
        'max_attachment_size',
        'max_total_attachment_size',
        'max_attachment_count',
        'template_size_limit',
        'exp_template_size_limit',
        'max_template_file',
        'exp_max_template_file',
        'file_mail_size_single',
        'file_mail_size_total',
        'file_mail_count',
        'file_mail_delete_days',
        /*PAC_5-1807 S*/
        'bbs_max_attachment_size',
        'bbs_max_total_attachment_size',
        'bbs_max_attachment_count',
        /*PAC_5-1807 S*/
        'max_frm_document',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules(){
        return [
            'mst_company_id' => 'required|numeric',
            'max_requests' => 'required|numeric',
            'max_document_size' => 'required|numeric|min:1|max:20',
            //'user_storage_size' => 'required|numeric',
            'use_storage_percent' => 'required|integer|numeric|min:1|max:100',
            'max_keep_days' => 'required|numeric',
            'delete_informed_days_ago' => 'required|numeric',
            'long_term_storage_percent' => 'required|integer|numeric|min:1|max:100',
            'dl_max_keep_days' => 'required|integer|numeric|min:0|max:65535',
            'dl_after_proc' => 'required|integer|numeric|min:0|max:1',
            'dl_after_keep_days' => 'required|integer|numeric|min:0|max:65535',
            'dl_request_limit' => 'required|integer|numeric|min:0|max:65535',
            'dl_request_limit_per_one_hour' => 'required|integer|numeric|min:0|max:65535',
            'dl_file_total_size_limit' => 'required|integer|numeric|min:0|max:10485760',
            'max_ip_address_count' => 'required|integer|numeric|min:1',
            'max_viwer_count' => 'required|integer|numeric|min:1',
            'sanitize_request_limit' => 'required|integer|numeric|min:0|max:65535',
            'max_attachment_size' => 'required|integer|numeric|min:1|max:500',
            'max_total_attachment_size' => 'required|integer|numeric|min:1',
            'max_attachment_count' => 'required|integer|numeric|min:1',
            'template_size_limit' => 'required|integer|numeric|min:0|max:2147483647',
            'exp_template_size_limit' => 'required|integer|numeric|min:0|max:2147483647',
            'max_template_file' => 'required|integer|numeric|min:0|max:2147483647',
            'exp_max_template_file' => 'required|integer|numeric|min:0|max:2147483647',
            /*PAC_5-1807 S*/
            'bbs_max_attachment_size' => 'required|numeric|min:1',
            'bbs_max_total_attachment_size' => 'required|numeric|min:1',
            'bbs_max_attachment_count' => 'required|numeric|min:0',
            /*PAC_5-1807 E*/
            'max_frm_document' => 'required|integer|numeric|min:0|max:2147483647',
        ];
    }
}

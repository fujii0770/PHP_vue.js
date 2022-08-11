<?php

namespace App\Repositories;

use App\Models\FrmTemplateCircularUser;
use App\Repositories\BaseRepository;

/**
 * Class CircularUserRepository
 * @package App\Repositories
 * @version November 20, 2019, 10:16 am UTC
*/

class FrmTemplateCircularUserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'frm_template_id',
        'parent_send_order',
        'child_send_order',
        'env_flg',
        'edition_flg',
        'server_flg',
        'mst_company_id',
        'mst_company_name',
        'mst_user_id',
        'email',
        'name',
        'title',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return FrmTemplateCircularUser::class;
    }
}

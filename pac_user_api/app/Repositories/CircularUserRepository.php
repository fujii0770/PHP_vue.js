<?php

namespace App\Repositories;

use App\Models\CircularUser;
use App\Repositories\BaseRepository;

/**
 * Class CircularUserRepository
 * @package App\Repositories
 * @version November 20, 2019, 10:16 am UTC
*/

class CircularUserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'circular_id',
        'parent_send_order',
        'child_send_order',
        'env_flg',
        'edition_flg',
        'server_flg',
        'mst_company_id',
        'mst_user_id',
        'email',
        'name',
        'title',
        'del_flg',
        'circular_status',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
        'received_date',
        'sent_date',
        'plan_id',
        'special_site_receive_flg',
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
        return CircularUser::class;
    }
}

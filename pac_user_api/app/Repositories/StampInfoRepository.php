<?php

namespace App\Repositories;

use App\Models\StampInfo;
use App\Repositories\BaseRepository;

/**
 * Class StampInfoRepository
 * @package App\Repositories
 * @version November 15, 2019, 3:18 am UTC
*/

class StampInfoRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'circular_document_id',
        'stamp_image',
        'name',
        'email',
        'info_id',
        'serial',
        'file_name',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
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
        return StampInfo::class;
    }
}

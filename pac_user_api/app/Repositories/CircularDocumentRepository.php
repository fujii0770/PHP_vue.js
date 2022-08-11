<?php

namespace App\Repositories;

use App\Models\CircularDocument;
use App\Repositories\BaseRepository;

/**
 * Class CircularDocumentRepository
 * @package App\Repositories
 * @version November 25, 2019, 3:40 am UTC
*/

class CircularDocumentRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'circular_id',
        'origin_document_id',
        'confidential_flg',
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
        return CircularDocument::class;
    }
}

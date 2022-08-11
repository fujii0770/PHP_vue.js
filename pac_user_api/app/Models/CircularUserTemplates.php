<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Favorite
 * @package App\Models

 */
class CircularUserTemplates extends Model
{
    public $table = 'circular_user_templates';
    
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';
}

<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Favorite
 * @package App\Models

 */
class CircularUserRoutes extends Model
{
    public $table = 'circular_user_routes';
    
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';
}

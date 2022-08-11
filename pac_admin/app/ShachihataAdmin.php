<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class ShachihataAdmin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $guard_name = 'web';

    protected $table = 'mst_shachihata';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */ 
    protected $casts = [

    ];

    public function getFullName()
    {
        return $this->name;
    }
}

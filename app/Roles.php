<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_admin',
        'view_other_user',
        'update_other_user',
        'activate_user',
        'view_student',
        'update_student',
        'update_exam',
        'send_emails',
        'update_results',
    ];

    public function users()
    {
        return $this->belongsToMany('App\User', 'users_roles');
    }
}

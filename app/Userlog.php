<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class Userlog extends Model
{
    protected $table = 'userlog';

    public function __construct(array $attributes = [])
    {
        $this->users_id= Auth::user()->id;
        $this->real_users_id= Auth::user()->getOriginalUser()->id;
        parent::__construct($attributes);
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'users_id', 'real_users_id', 'crud', 'action', 'old_value', 'new_value',
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function real_user()
    {
        return $this->hasOne('App\User', 'id', 'real_user_id');
    }


}

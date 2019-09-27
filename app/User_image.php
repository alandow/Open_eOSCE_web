<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User_image extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    // the table this model refers to. I think I'll be explicit with this by default.
    protected $table = 'users_image';

    //the fillable fields in the patients database. Protect against malicious code
    protected $fillable = [
        'user_id', 'name', 'type', 'size', 'description', 'path',
    ];


}

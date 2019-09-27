<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;

class Group extends Model
{

    use SoftDeletes;
    protected $table = 'groups';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text', 'code'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public function __construct()
    {

    }

}

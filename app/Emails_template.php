<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emails_template extends Model
{
    use SoftDeletes;


    protected $table = 'email_templates';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'subject', 'notes', 'text', 'context'
    ];

    public function logs()
    {
        return $this->hasMany('App\Emails_log', 'email_id', 'id');
    }
}

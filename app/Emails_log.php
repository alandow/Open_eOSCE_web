<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emails_log extends Model
{

    protected $table = 'email_logs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_id', 'instance_id', 'sent_to_id', 'sent_by_id', 'created_at', 'context', 'fulltext'
    ];

    public function template()
    {
        return $this->belongsTo('App\Emails_template', 'email_id', 'id');
    }

    public function sent_to()
    {
        return $this->belongsTo('App\User', 'sent_to_id', 'id');
    }

    public function sent_by()
    {
        return $this->belongsTo('App\User', 'sent_by_id', 'id');
    }

}

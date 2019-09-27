<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exam_instance_item_item extends Model
{

    protected $table = 'exam_instance_items_items';

    protected $fillable = [
        'exam_instance_items_id',
        'label',
        'description',
        'value',
        'order',
        'needscomment',
        'last_updated_by',
    ];

    public function exam_instance_item()
    {
        return $this->belongsTo('App\Exam_instance_item', 'exam_instance_items_id', 'id');
    }


}

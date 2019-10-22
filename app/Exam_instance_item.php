<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Exam_instance_item extends Model
{
    use SoftDeletes;
    use Sortable;

    protected $table = 'exam_instance_items';

    protected $fillable = [
        'is_template',
        'label',
        'description',
        'order',
        'heading',
        'show_if_id',
        'show_if_answer_id',
        'exam_instance_id',
        'no_comment',
        'exclude_from_total',
        'last_updated_by',
    ];

    public function exam_instance()
    {
        return $this->belongsTo('App\Exam_instance', 'exam_instance_id', 'id');
    }

    public function items()
    {
        return $this->hasMany('App\Exam_instance_item_item', 'exam_instance_items_id', 'id');
    }



    public function scopeScorable($query){
        $q= $query->whereRaw("exclude_from_total is null")
            ->whereRaw("heading is null");
return $q;
    }

}

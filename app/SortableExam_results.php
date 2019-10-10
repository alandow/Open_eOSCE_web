<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * Class SortableExam_results
 * @package App
 * A sotrtable view of an exam results.
 */
class SortableExam_results extends Model
{
    //
    use SoftDeletes;
    use Sortable;

    protected $table = 'sortable_exam_results';

    protected $fillable = [

    ];

    protected $sortable = ['studentname',
        'created_by',
        'total',
        'created_at',
        'created_by',
        'start_datetime',
        'end_datetime',
        'groupcode'
    ];

    public function student()
    {
        return $this->hasOne('App\Student', 'id', 'student_id');
    }

    public function group()
    {
        return $this->hasOne('App\Group', 'id', 'group_id');
    }

}


<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Student extends Model
{
    //
    use SoftDeletes;
    use Sortable;

    protected $table = 'students';

    protected $sortable = ['studentid',
        'fname',
        'lname',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fname', 'lname', 'email', 'studentid',
    ];

    public function examinations_assigned()
    {
        return $this->belongsToMany('App\Exam_instance', 'student_exam_instances', 'students_id', 'exam_instances_id')->withPivot('id', 'group_id')->withTimestamps();
    }

    public function student_exam_submission()
    {
        return $this->hasMany('App\Student_exam_submission', 'student_id', 'id');
    }

    public function image()
    {
        return $this->hasOne('App\Student_image', 'student_id', 'id');
    }

    // filter to return students that should show up in a search query for an exam
    public function scopeStudentsforexam($query, $examid)
    {

        $query
            // co-ordinators
            ->whereHas('examinations_assigned', function ($query) use ($examid) {
                $query->where('exam_instances_id', '=', $examid);
            })
        ->whereDoesntHave('student_exam_submission', function ($query) use ($examid) {
            $query->where('exam_instances_id', '=', $examid);
            });

        return $query;
    }
}

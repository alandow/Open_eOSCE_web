<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student_exam_submission extends Model
{
    use SoftDeletes;

    protected $table = 'student_exam_submissions';

    protected $fillable = [
        'student_id',
        'exam_instances_id',
        'created_by',
        'comments',
        'status',
        'group_id',
    ];

    public function student()
    {
        return $this->hasOne('App\Student', 'id', 'student_id');
    }

    public function group()
    {
        return $this->hasOne('App\Group', 'id', 'group_id');
    }

    public function exam_instance()
    {
        return $this->hasOne('App\Exam_instance', 'id', 'exam_instances_id');
    }

    public function examiner()
    {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function student_exam_submission_items()
    {
        return $this->hasMany('App\Student_exam_submission_item', 'student_exam_submissions_id', 'id');
    }

    public function student_exam_submission_values()
    {
        return $this->hasManyThrough('App\Student_exam_submission_item', 'student_exam_submissions_id', 'id');
    }

    public function changelog()
    {
        return $this->hasMany('App\Student_exam_submission_changelog', 'student_exam_submissions_id', 'id');
    }

//    public function scopeScoreditems($query){
//        return $query->whereHas('student_exam_submission_items', function ($q)  {
//            $q->whereHas('item', function($q2){{
//                $q2->where('exclude_from_total', '<=>', '1');
//            }});
//        });
//    }
//
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student_exam_submission_changelog extends Model
{
    use SoftDeletes;

    protected $table = 'student_exam_submissions_changelog';

    protected $fillable = [
        'student_exam_submissions_id',
        'updated_by',
        'old_comments',
        'reason',
    ];

    public function student_exam_submission()
    {
        return $this->hasOne('App\Student_exam_submission', 'id', 'student_id');
    }

    public function updated_by()
    {
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}

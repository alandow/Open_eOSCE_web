<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Student_exam_submission_item_changelog extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        $this->updated_by= Auth::user()->id;
        $this->real_updated_by = Auth::user()->getOriginalUser()->id;
        parent::__construct($attributes);
    }

    protected $table = 'student_exam_submissions_items_changelog';

    protected $fillable = [
        'student_exam_submissions_items_id',
        'selected_exam_instance_items_items_id',
        'old_selected_exam_instance_items_items_id',
        'updated_by',
        'real_updated_by',
        'comments',
        'old_comments',
        'reason',
    ];

    public function student_exam_submission()
    {
        return $this->hasOne('App\Student_exam_submission_item', 'id', 'student_id');
    }

    public function updated_by()
    {
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}

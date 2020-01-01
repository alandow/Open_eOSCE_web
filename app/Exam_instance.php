<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * Class Exam_session
 * @package App
 * Represents the examinations that teh students are assigned to
 */
class Exam_instance extends Model
{
    //
    use SoftDeletes;
    use Sortable;

    protected $table = 'exam_instances';

    protected $fillable = [
        'name',
        'description',
        'is_template',
        'notes',
        'start_datetime',
        'end_datetime',
        'created_by_id',
        'owned_by_id',
        'status',
        'finalised',
        'emailtext',
        'unit_id',
        'archived_at',
        'last_updated_by_id',
        'email_parameters',
        'email_template_id'
    ];

    protected $sortable = ['name',
        'studentname',
        'unit_id',
        'owned_by_id',
        'created_at',
        'start_datetime',
        'end_datetime',
        'status',
    ];


    public function exam_instance_items()
    {
        return $this->hasMany('App\Exam_instance_item', 'exam_instance_id', 'id')->orderBy('order', 'asc');
    }
//
//    public function assessment_criteria_scale()
//    {
//        return $this->belongsTo('App\Criteria_scale_type', 'id', 'assessment_criteria_scale_id');
//    }
//
    public function feedback_template()
    {
        return $this->hasOne('App\Emails_template', 'id', 'email_template_id');
    }
//

    public function owner()
    {
        return $this->belongsTo('App\User', 'owned_by_id', 'id');
    }

//    public function rating_scale()
//    {
//        return $this->belongsTo('App\Criteria_scale_type', 'assessment_criteria_scale_id', 'id');
//    }

    public function created_by()
    {
        return $this->belongsTo('App\User', 'created_by_id', 'id');
    }

    public function last_updated_by()
    {
        return $this->belongsTo('App\Unit_lookup', 'modified_by_id', 'id');
    }

    public function student_exam_submissions()
    {
        return $this->hasMany('App\Student_exam_submission', 'exam_instances_id', 'id');
    }

    public function sortable_student_exam_submissions()
    {
        return $this->hasMany('App\SortableExam_results', 'exam_instances_id', 'id');
    }

    public function examiners()
    {
        return $this->belongsToMany('App\User', 'users_exams_instances', 'exam_instances_id', 'users_id')->withPivot('id', 'users_id');
    }

    public function students()
    {
        return $this->belongsToMany('App\Student', 'student_exam_instances', 'exam_instances_id', 'students_id')->withPivot('id', 'group_id');
    }

    public function GetGroupIDsAttribute(){
        return $this->students()->groupBy('group_id')->pluck('group_id');
    }

    public function GetEmailLogAttribute(){
        return Emails_log::where(['instance_id'=> $this->id])->get();
    }

    public function scopeScorableitems($query){
        return $query-> whereHas('exam_instance_items', function ($query)  {
            $query->where('exclude_from_total', '<>', '1')
                ->orWhere('heading', '<>', '1');
        });
    }

    // mutators. Data in table is stored as UNIX timestamps for certain things, this is how we'll deal with it
//    public function getCreatedTimestampAttribute($value)
//    {
//        return date('j/m/Y', $value);
//    }

// logging
    protected static function boot()
    {
        static::updating(function ($instance) {
            Userlog::create(['crud'=>'update', 'action'=>'Exam Instance', 'new_value'=>$instance->name, 'old_value'=>$instance->getOriginal('name')])->save();
        });

        static::creating(function ($instance) {
            Userlog::create(['crud'=>'create', 'action'=>'Exam Instance', 'new_value'=>$instance->name])->save();
        });
        static::deleting(function ($instance) {
            Userlog::create(['crud'=>'delete', 'action'=>'Exam Instance', 'old_value'=>$instance->name])->save();
        });
    }

    // sortable
    public function studentnameSortable($query, $direction)
    {
        return $query->join('students', 'students.id', '=', 'sortable_exam_results.student_id')
            ->orderBy('student_id', $direction)
            ->select('sortable_exam_results.*');
    }
}


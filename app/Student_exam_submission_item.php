<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Student_exam_submission_item extends Model
{
    use SoftDeletes;

    protected $table = 'student_exam_submissions_items';


    protected $fillable = [
        'student_exam_submissions_id',
        'exam_instance_items_id',
        'selected_exam_instance_items_items_id',
        'comments',
        'reason'
    ];

    public function student_exam_submission()
    {
        return $this->hasOne('App\Student_exam_submission', 'id', 'student_id');
    }

    public function changelog()
    {
        return $this->hasMany('App\Student_exam_submission_item_changelog', 'student_exam_submissions_items_id', 'id');
    }

    public function item()
    {
        return $this->hasOne('App\Exam_instance_item', 'id', 'exam_instance_items_id');
    }

    public function selecteditem()
    {
        return $this->hasOne('App\Exam_instance_item_item', 'id', 'selected_exam_instance_items_items_id');
    }




// logging
    protected static function boot()
    {
        static::updating(function ($instance) {
            Userlog::create(['crud' => 'update', 'action' => 'Student exam submission item', 'new_value' => $instance->selecteditem->label, 'old_value' => Exam_instance_item_item::find($instance->getOriginal('selected_exam_instance_items_items_id'))->label])->save();
            Student_exam_submission_item_changelog::create(['student_exam_submissions_items_id' => $instance->id,
                'selected_exam_instance_items_items_id' => $instance->selected_exam_instance_items_items_id,
                'old_selected_exam_instance_items_items_id' => $instance->getOriginal('selected_exam_instance_items_items_id'),
                'comments' => $instance->comments,
                'old_comments' => $instance->getOriginal('comments'),
                'reason' => $instance->reason])->save();
        });

        static::creating(function ($instance) {
            Log::error(json_encode($instance));
            Userlog::create(['crud' => 'create', 'action' => 'Student exam submission item', 'new_value' => 'Label:' . (isset($instance->selecteditem)?$instance->selecteditem->label:'') . ', Comment:' . (isset($instance->comments)?$instance->comments:'')])->save();
        });
        static::deleting(function ($instance) {
            Userlog::create(['crud' => 'delete', 'action' => 'Student exam submission item', 'old_value' => $instance->id])->save();
        });
    }
}

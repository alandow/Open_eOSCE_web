<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student_image extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    // the table this model refers to. I think I'll be explicit with this by default.
    protected $table = 'students_images';

    //the fillable fields in the patients database. Protect against malicious code
    protected $fillable = [
        'student_id', 'path', 'filename'
    ];

    /**
     * A media record is owned by a user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->hasOne('App\Student', 'student_id', 'id');
    }
}

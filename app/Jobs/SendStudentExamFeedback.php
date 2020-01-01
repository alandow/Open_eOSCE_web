<?php

namespace App\Jobs;

use App\Emails_log;
use App\Mail\StudentExamFeedback;
use App\Student;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendStudentExamFeedback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $template;
    protected $submission_id;
    protected $to_student_id;
    protected $from_id;
    protected $testing;
    protected $testing_to_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StudentExamFeedback $email, $to_student_id, $from_id, $template, $submission_id, $testing, $testing_to_id)
    {
        $this->email = $email;
        $this->template = $template;
        $this->submission_id = $submission_id;
        $this->to_student_id = $to_student_id;
        $this->from_id = $from_id;
        $this->testing = $testing;
        $this->testing_to_id = $testing_to_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//dd(User::find($this->testing_to_id)->email);
        if($this->testing){
            Mail::to(User::find($this->testing_to_id)->email)->send($this->email);
        }else{
            Mail::to(Student::find($this->to_id)->email)->send($this->email);
        }
        $log = new Emails_log();
        $log->email_id = $this->template->id;
        $log->instance_id = $this->submission_id;
        $log->context = 'studentfeedback';
        $log->sent_to_id = $this->testing?$this->from_id:$this->to_student_id;
        $log->sent_by_id = $this->from_id;
        $log->fulltext = $this->email->getfulltext();
        $log->save();
//
    }
}

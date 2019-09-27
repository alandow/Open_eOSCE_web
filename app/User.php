<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use Sortable;
    use HasApiTokens;

    protected $app;
    protected $auth;
    protected $session;
    protected $sessionKey = 'sudosu.original_id';
    protected $usersCached = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'email', 'password', 'type', 'active', 'notes',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->session = session();
        //   dd($this->session);
    }

    public function getRoleslistAttribute()
    {
        return $this->roles();
    }

    public function roles()
    {
        return $this->belongsToMany('App\Roles', 'users_roles');
    }

    public function image()
    {
        return $this->hasOne('App\User_image', 'user_id', 'id');
    }

    public function exam_instances()
    {
        return $this->belongsToMany('App\Exam_instance', 'users_exams_instances', 'users_id', 'exam_instances_id');
    }

    public function user_log()
    {
        return $this->hasMany('App\Userlog', 'users_id', 'id')->orWhere('real_users_id','=',$this->id);
    }


    public function loginAsUser($userId, $currentUserId)
    {
        $this->session->put('sudosu.has_sudoed', true);
        $this->session->put($this->sessionKey, $currentUserId);

        Auth::loginUsingId($userId);
    }

    public function injectToView($user)
    {
        $packageContent = view('auth.user-selector', [
            'hasSudoed' => $this->hasSudoed(),
            'originalUser' => $this->getOriginalUser(),
            'currentUser' => Auth::user(),
            'users' => \App\User::all(),
            'user' => $user
        ])->render();

        return $packageContent;
    }

    public function hasSudoed()
    {
        return $this->session->has('sudosu.has_sudoed');
    }

    public function getOriginalUser()
    {
        if (!$this->hasSudoed()) {
            return $this;
        }

        $userId = $this->session->get($this->sessionKey);

        return $this::findOrFail($userId);
    }

    public function returnToUser()
    {
        if (!$this->hasSudoed()) {
            return false;
        }

        Auth()->logout();

        $originalUserId = $this->session->get($this->sessionKey);

        if ($originalUserId) {
            Auth()->loginUsingId($originalUserId);
        }

        $this->session->forget($this->sessionKey);
        $this->session->forget('sudosu.has_sudoed');

        return true;
    }

    public function findForPassport($username) {
        return self::where('username', $username)->first(); // change column name whatever you use in credentials
    }

}

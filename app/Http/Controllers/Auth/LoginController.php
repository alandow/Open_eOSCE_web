<?php

namespace App\Http\Controllers\Auth;

use Adldap\Laravel\Facades\Adldap;
use App\Http\Controllers\Controller;
use App\User;
use App\Userlog;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * @var Adldap
     */
    // protected $adldap;

    public function username()
    {
        return 'username';
    }

    use AuthenticatesUsers {
        // expose the login method from AuthenticatesUsers
        // method as exposedmethod
        login as parentlogin;
    }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
//    public function __construct(AdldapInterface $adldap)
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    //override login, insert ldap here


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        //  $this->adldap = $adldap;
    }

    // post authenticated action. We're using this to log attempts
    public function authenticated($request, $user )
    {
      // dd($user);
       Userlog::create(['action'=>'login'])->save();
    }

    public function login(\Illuminate\Http\Request $data)
    {
        $input = $data->all();
        //   dd($input);
// check that this user exists
        $user = User::where('username', '=', $input['username'])->first();
        if ($user === null) {
            // user not found- return usual errors
            return $this->parentlogin($data);
        } else {
            switch ($user->type) {
                case 'ldap':
//                    if(Adldap::auth()->attempt($input['username'], $input['password'])){
//                        // password is not saved locally: we'll accept the LDAP credentials
//                        Auth::loginUsingId($user->id);
//                        return Redirect::intended('home');
//                    }else{
//                        $errors = [$this->username() => trans('auth.failed')];
//                        return redirect()->back()
//                            ->withErrors($errors);
//                    }
//                    break;

                case 'manual':
                   // $user = $this->attemptLogin($data->username, $data->password);
                return  $this->parentlogin($data);
                    break;

                default:
                    return $this->parentlogin($data);
                    break;

            }

        }

    }


}

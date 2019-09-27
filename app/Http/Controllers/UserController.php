<?php

namespace App\Http\Controllers;

use Adldap\AdldapInterface;
use App\Exam_instance;
use App\User;
use App\Userlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Request;

class UserController extends Controller
{


    /**
     * @var Adldap
     */
    protected $adldap;

    /**
     * Constructor.
     *
     * @param AdldapInterface $adldap
     */
//    public function __construct(AdldapInterface $adldap)
//    {
//        $this->middleware('auth');
//        $this->adldap = $adldap;
//
//    }
    public function __construct()
    {
        $this->middleware('auth');

    }

    public function index(Request $request)
    {
        if (!Auth::user()->can('view_other_user')) {

            return redirect('home');
        }
        $params = $request::all();

        $user = User::orderBy('name')->paginate(20);
        $users = $user;

        if (isset($params['search'])) {
            $user = User::where('username', 'like', "%{$params['search']}%")
                ->orWhere('name', 'like', "%{$params['search']}%")->orderBy('name');
            $users = $user->sortable()->paginate(20);
        } else {
            $user = User::orderBy('name')->sortable()->paginate(20);
            $users = $user;
        }

        $roles = \App\Roles::all();

        return view("user.list")->with('users', $users)->with('roles', $roles);
    }

    public function show($id)
    {
        //   dd((Auth::user()->can('view_other_user')));
        if ((Auth::user()->id == $id) || Auth::user()->can('view_other_user')) {
            $user = \App\User::findOrFail($id);
            $roles = \App\Roles::all();

            //  dd($user->roles);
            return view("user.view")->with('user', $user)->with('roles', $roles);
        } else {
            return redirect()->back();
        }
    }

    public function my()
    {
        //   dd((Auth::user()->can('view_other_user')));
        if (isset(Auth::user()->id)) {
            $user = Auth::user();
            $roles = \App\Roles::all();

            //  dd($user->roles);
            return view("user.view")->with('user', $user)->with('roles', $roles);
        } else {
            return redirect()->back();
        }
    }

    public function getUser($id)
    {
        $user = \App\User::findOrFail($id);
        $roleids = [];
        foreach ($user->roles as $role) {
            $roleids[] = $role->pivot->roles_id;
        }
        $user['role_id'] = implode(',', $roleids);
        //dd($user['role_ids']);
        return $user;
    }

    public function ajaxstore(Request $request)
    {
$log = new Userlog();
        if (Auth::user()->can('update_other_user')) {
            //$this->authorize('create', User::class);
            $input = $request::all();

            // find out if there's an existing user
            $user = \App\User::where('username', $input['username'])->first();
            if (isset($user)) {
                $response = array(
                    'status' => '-1',
                    'statusText' => 'that user already exists',
                );
            } else {
                $newuser = new User();
                if ($input['type'] == 'manual') {
                    $newuser->name = $input['name'];
                    $newuser->email = $input['email'];
                    $newuser->password = Hash::make($input['password']);
                } else {
                    // get AD values for user here

                    $userdetails = $this->getDetailsByUsername($input['username']);
                    if ($userdetails !== NULL) {

                        $newuser->email = $userdetails["mail"][0];
                        $newuser->name = $userdetails["displayname"][0];

                    } else {
                        $response = array(
                            'status' => '-1',
                            'statusText' => 'User not found in directory',
                        );
                        return $response;
                    }

                }
                $newuser->username = $input['username'];

                $savestatus = $newuser->save();


                // Sync roles
                try {

                    $rolesupdate = $newuser->roles()->sync($input['role_id']);
                    $rolesupdatesuccess = (count($rolesupdate['attached'] > 0) || count($rolesupdate['detached'] > 0) || count($rolesupdate['attached'] > 0));
                    // update user
                    $roleupdatestatus = ($newuser->update($input) || $rolesupdatesuccess);

                } catch (\Exception $e) {
                    $response = array(
                        'status' => '-1',
                        'statusText' => $e->getMessage(),
                    );
                    return $response;
                }
                $response = array(
                    'status' => ($savestatus || $roleupdatestatus) ? 0 : -1,
                );
            }
            Userlog::create(['crud'=>'create', 'action'=>'User', 'new_value'=>$newuser->username])->save();
            return $response;
        } else {
            $response = array(
                'status' => '-1',
                'statusText' => 'unauthorised',
            );
            return $response;
        }

    }

    public function getDetailsByUsername($username)
    {
        // LDAP here
        try {
            $provider = $this->adldap;
            $search = $provider->search();
            return $search->findBy('uid', $username);
            //  dd($record);

        } catch (Exception $e) {
            // something went wrong with AD
            dd($e);
        }
        //dd($input);
    }

    /**
     * @param Request $request
     * @return array
     */
    public
    function ajaxupdate(Request $request)
    {

        $input = $request::all();

        //   dd($input);

        $user = \App\User::find($input['id']);


        if (isset($input['type'])) {

            if ($input['type'] == 'manual') {
                if (strlen($input["password"]) > 0) {
                    $input['password'] = Hash::make($input['password']);
                } else {
                    unset($input['password']);
                }
            } else {
                // get AD values for user here
                $userdetails = $this::getDetailsByUsername($input['username']);
                //  dd($userdetails);
                if ($userdetails !== NULL) {

                    $user->email = $userdetails["mail"][0];
                    $user->name = $userdetails["displayname"][0];

                } else {
                    $response = array(
                        'status' => '-1',
                        'statusText' => 'User not found in directory',
                    );
                    return $response;
                }
            }
        } else {
            // must be 'my' page
            if (strlen($input["password"]) > 0) {
                $input['password'] = Hash::make($input['password']);
            } else {
                unset($input['password']);
            }
            $user->email = $input["email"];
            $user->name = $input["name"];
        }

        $savestatus = $user->save();
        $rolesupdatesuccess = false;
        if (isset($input['role_id'])) {
            try {

                $rolesupdate = $user->roles()->sync($input['role_id']);
                $rolesupdatesuccess = (count($rolesupdate['attached'] > 0) || count($rolesupdate['detached'] > 0) || count($rolesupdate['attached'] > 0));
                // update user
                $roleupdatestatus = ($user->update($input) || $rolesupdatesuccess);

            } catch (\Exception $e) {
                $response = array(
                    'status' => '-1',
                    'statusText' => $e->getMessage(),
                );
                return $response;
            }
        }
        $response = array(
            'status' => ($savestatus || $roleupdatestatus) ? '0' : -1,
        );
        return $response;
    }

    public
    function ajaxdestroy(Request $request)
    {
        //   dd($request);
        if (Gate::denies('is_admin')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        return \App\User::destroy($input['id']);
    }

    public function select2search(Request $request)
    {
        $input = $request::all();
        $dontshow = [];
        if (isset($input['exam_instance_id'])) {
            $dontshow = Exam_instance::find($input['exam_instance_id'])->examiners->pluck('id')->toArray();
            $results = User::where('username', 'like', "%{$input['q']}%")
                ->orWhere('name', 'like', "%{$input['q']}%")
                ->get();
        } else {
            $results = User::where('username', 'like', "%{$input['q']}%")
                ->orWhere('name', 'like', "%{$input['q']}%")->orderBy('name')->get();
        }

        $returnStr = '{
  "results": [';
        foreach ($results as $result) {
            // could probably do this better, as part of the query. But, this works for now
            if (!(in_array($result['id'], $dontshow))) {
                $returnStr .= '{"id": "' . $result['id'] . '", "text": "' . $result['name'] . '"},';
            }
        }
        $returnStr = rtrim($returnStr, ',');
        //array_map(function($n){return (object) ['id' => $n->id, 'text'=>$n->name ];},);
        return ($returnStr . ']
}');

    }
    ////////////////////////////////////////////////////////////////////////
    //
    //LDAP integration
    //
    /////////////////////////////////////////////////////////////////////////
    // tap into the LDAP server here

    /**
     * @param Request $request
     * @return array
     */
    public function activate(Request $request)
    {
        if (Gate::denies('activate_user')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        $user = \App\User::findOrFail($input['id']);
        if (isset($user->active)) {
            $user->active = ($user->active == 'true') ? 'false' : 'true';
        } else {
            $user->active = 'true';
        }
        return array(
            'status' => $user->update() ? '0' : '-1'
        );

    }

/////////////////////////////////////////////////////////////////////////////
//
//Logging in as other users. This is a very high-level (and kinda risky) thing to allow,
// but it might be useful in the context of a system that has users of differing abilities
//eOSCE spring to mind, but we'll need to log it
//
////////////////////////////////////////////////////////////////////////////

    /**
     * Log in as a user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAsUser(Request $request)
    {
        $input = $request::all();
        Auth::user()->loginAsUser($input['userId'], $input['originalUserId']);
// @TODO log this action- who logged in as who
        return redirect()->back();
    }

    /**
     * Return to the previously logged in user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function returnToUser(Request $request)
    {
        Auth::user()->returnToUser();
// @TODO log this action- who logged out and as who
        return redirect()->back();
    }
}

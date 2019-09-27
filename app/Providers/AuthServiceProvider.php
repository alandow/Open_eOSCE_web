<?php

namespace App\Providers;

use App\Roles;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
//        Route::group([ 'middleware' => 'cors'], function() {
//
//            Passport::routes();
//
//        });
        Passport::tokensExpireIn(Carbon::now()->addDays(1));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(1));

        // support for roles
        $capabilities = Roles::first()->getFillable();
        //   dd($capabilities);

        foreach ($capabilities as $capability) {
            Gate::define($capability, function ($user) use ($capability) {

                // check that one of the roles contains admin
                $admin = $user->roles->first(function ($value, $key) {
                    return $value['is_admin'] == 'true';
                });
                // if admin, just return true. We don't need to go any further
                if (isset($admin)) {
                    return true;
                }
                // now check that any of the other roles contains teh capability
                $role = $user->roles->first(function ($value, $key) use ($capability) {
                    //  dd($value[$capability]);
                    return $value[$capability] == 'true';
                });

                return (isset($role));

            });
        }
    }
}

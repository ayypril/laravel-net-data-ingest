<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToDiscordOauth(){
        return Socialite::driver('discord')->redirect();
    }

    /**
     * @param $url
     * @return string|string[]
     */
    private function getUserAvatarUrl($url){
        /*
         * we use this to see if a user has a gif avatar
         * discord avatars begin with an a if they are animated.
         * https://discord.com/developers/docs/reference#image-formatting-image-formats
         */

        $string = substr($url, strrpos($url, '/') + 1);
        if(substr( $string, 0, 1 ) === "a"){
            $avatar = substr_replace($url, 'gif', strrpos($url, '.') +1);
        }
        else {
            $avatar = $url;
        }
        return $avatar;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function processDiscordCallback(Request $request){
        // get oauth info
        // catch in case user denies consent
        try {
            $info = Socialite::driver('discord')->user();
            // https://github.com/SocialiteProviders/Discord
            // returned user fields: id, nickname, name, email, avatar
        } catch (\Throwable $e){
            return redirect('/login');
            //return response('Oops! We had an issue getting your user information. Did you cancel the oauth login?', 400);

        }



        // attempt to get user info in case user already exists
        $user = User::where(['discord_id' => $info->id])->first();

        // if user already exists

        if ($user) {
            // log user in
            Auth::login($user);

            // update user name, email, & avatar in database

            $userinfo = User::where(['discord_id' => $info->id])->first();
            $userinfo->name = $info->name;
            $userinfo->email = $info->email;

            $userinfo->avatar = $this->getUserAvatarUrl($info->avatar);
            $userinfo->last_ip = $request->ip();
            $userinfo->save();
            return redirect()->intended('home');

        } else {
            // create new user utilizing information
            $newuser = new User();
            $newuser->discord_id = $info->id;
            $newuser->name = $info->name;
            $newuser->email = $info->email;
            $newuser->avatar = $this->getUserAvatarUrl($info->avatar);
            $newuser->last_ip = $request->ip();
            $newuser->save();

            // get newly created user object and log in
            $user = User::where(['discord_id' => $info->id])->first();
            Auth::login($user);
            // return to intended url - otherwise, fall back to account page.
            return redirect()->intended('home');
        }
    }
}

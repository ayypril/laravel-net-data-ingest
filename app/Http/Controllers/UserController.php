<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    // display user page
    public function index(Request $request){
        return view('account.index');
    }

    public function loadKeysPage(Request $request){
    }


    // DELETE /account
    public function deleteAccount(Request $request){
        $user = Auth::user();
        $user->delete();
        return response('OK', 200);
    }


}

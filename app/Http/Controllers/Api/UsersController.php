<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function create(Request $request)
    {
        /**
        * Get a validator for an incoming registration request.
        *
        * @param  array  $request
        * @return \Illuminate\Contracts\Validation\Validator
        */
       $valid = validator($request->only('email', 'full_name', 'password', 'role'), [
           'full_name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'password' => 'required|string',
           'role' => 'nullable|string|in:admin,manager,user',
        ],[
            'role.in' => 'Invalid Role.'
        ]);

       if ($valid->fails()) {
           $jsonError=response()->json($valid->errors()->all(), 400);
           return \Response::json($jsonError);
       }

       $data = request()->only('email','full_name','password', 'role');

       $user = User::create([
           'full_name' => $data['full_name'],
           'email' => $data['email'],
           'password' => bcrypt($data['password']),
           'role' => $data['role']
       ]);

       // And created user until here.

       // $client = Client::where('password_client', 1)->first();

       // Is this $request the same request? I mean Request $request? Then wouldn't it mess the other $request stuff? Also how did you pass it on the $request in $proxy? Wouldn't Request::create() just create a new thing?

       /* $request->request->add([
           'grant_type'    => 'password',
           'client_id'     => $client->id,
           'client_secret' => $client->secret,
           'username'      => $data['email'],
           'password'      => $data['password'],
           'scope'         => null,
       ]); */
       
       $oauth_client = DB::table('oauth_clients')                 
                 ->where('id', '=', 2)
                 ->first();
       
       $request->request->add([
           'grant_type'    => 'password',
           'client_id'     => $oauth_client->id,
           'client_secret' => $oauth_client->secret,
           'username'      => $data['email'],
           'password'      => $data['password'],
           'scope'         => null,
       ]);

       // Fire off the internal request. 
       $token = Request::create(
           'oauth/token',
           'POST'
       );
       return \Route::dispatch($token);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'            
        ]);

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addDays(1);

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    public function getUser(Request $request)
    {
        // echo json_encode('test');
        return $request->user();
    }

    public function edit(Request $request)
    {
        $id = $request['id'];
        $user = $request->user();
        $role = $user->role;
        $check = User::checkPermission($role);
        if($check == true)
        {
            $user_count = User::where('id',$id)->count();
            if($user_count == 1)
            {
                $valid = validator($request->only('email', 'full_name', 'password', 'role'), [
                    'full_name' => 'required|string|max:255',
                    'password' => 'nullable|string|max:255',
                    'email' => 'required|string|email|max:255|'.Rule::unique('users', 'email')->ignore($id),
                    'role' => 'nullable|string|in:admin,manager,user',
                ],[
                    'role.in' => 'Invalid Role.'
                ]);

                if ($valid->fails()) {
                    $jsonError=response()->json($valid->errors()->all(), 400);
                    return \Response::json($jsonError);
                }

                $full_name = $request['full_name'];
                $email = $request['email'];
                $password = $request['password'];
                $role = $request['role'];

                $users = User::find($id);
                if(isset($full_name))
                {   $users->full_name = $full_name;     }
                if(isset($email))
                {   $users->email = $email;     }
                if(isset($password))
                {   $users->password = bcrypt($password);     }
                if(isset($role))
                {   $users->role = $role;     }
                $users->save();
                return \Response::json($users);
            }
            else
            {
                $msg = array("message" => "User does not Exists.");
                $jsonError = response()->json($msg, 400);
                return \Response::json($jsonError);
            }
        }
        else
        {
            $msg = array("message" => "You are not Authorized to use this API.");
            $jsonError = response()->json($msg, 400);
            return \Response::json($jsonError);
        }
    }

    public function delete(Request $request)
    {
        $id = $request['id'];
        $user = $request->user();
        $role = $user->role;
        $check = User::checkPermission($role);
        if($check == true)
        {
            $user_count = User::where('id',$id)->count();
            if($user_count == 1)
            {
                // Delete The Post created by User.
                Post::where('user_id', $id)->delete();

                // Delete The User
                $users = User::find($id)->delete();                
                $msg = array("message" => "User Successfully Deleted.");
                $jsonError = response()->json($msg, 400);
                return \Response::json($jsonError);
            }
            else
            {
                $msg = array("message" => "User does not Exists.");
                $jsonError = response()->json($msg, 400);
                return \Response::json($jsonError);
            }
        }
        else
        {
            $msg = array("message" => "You are not Authorized to use this API.");
            $jsonError = response()->json($msg, 400);
            return \Response::json($jsonError);
        }
    }

    public function listUsers(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $check = User::checkPermission($role);
        if($check == true)
        {
            $users = User::all();
            return \Response::json($users);
        }
        else
        {
            $msg = array("message" => "You are not Authorized to use this API.");
            $jsonError = response()->json($msg, 400);
            return \Response::json($jsonError);
        }
    }    
}

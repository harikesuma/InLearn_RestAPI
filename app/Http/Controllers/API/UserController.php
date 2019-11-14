<?php

namespace App\Http\Controllers\API;

use App\User; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Validator;


class UserController extends Controller
{
    public $successStatus = 200;

    public function login(){
        if(Auth::attempt(['user_name' => request('user_name'), 'password' => request('password')])){
            $user = Auth::user();
            $success['user_name'] = $user->user_name;
            $success['name'] = $user->name;
            $success['email'] = $user->email;
            $success['pict'] = $user->pict;
            $success['token'] =  $user->createToken('InLearnApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'user_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'jenis_kelamin' => 'required'
        
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        $input = $request->all();

        $count = User::where(['user_name' => $input['user_name']])->count();
        if($count) {
            $response['status'] = 401;
            $response['message'] = "Username already exist";
            return response()->json($response, 401);      
        }

        $count = User::where(['email' => $input['email']])->count();
        if($count) {
            $response['status'] = 401;
            $response['message'] = "Email already used";
            return response()->json($response, 401);      
        }
        
        
        $input['password'] = bcrypt($input['password']);
        $input['status'] = '1';
        $input['pict'] = 'not selected yet';
        
        $user = User::create($input);

        $success['token'] =  $user->createToken('InLearnApp')->accessToken;
        $success['name'] =  $user->name;
        $success['user_name'] =  $user->user_name;
        $success['email'] =  $user->email;

        return response()->json(['success'=>$success], $this->successStatus);
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }
}

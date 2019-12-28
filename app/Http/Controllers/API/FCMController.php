<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\FCMToken;
use Illuminate\Support\Facades\Auth;
use App\Notification;

class FCMController extends Controller
{
    public $successStatus = 200;


    public function insertToken($token){
        $count = FCMToken::where('user_id', Auth::user()->id)->where('fcm_token', $token)->count();
       
        if($count == 0){
            $input['user_id'] =  Auth::user()->id;
            $input['fcm_token'] = $token;
            $tokens = FCMToken::create($input); 
            $msg = $tokens->fcm_token;
        } else {
            $msg = 0;
        }
        return response()->json(['success' => $msg], $this->successStatus); 
    }

    public function deleteToken($token, Request $request) 
    { 
        $token = FCMToken::where('user_id', $request->user_id)->where('fcm_token', $token)->delete();
        return response()->json(['success' => $token], $this->successStatus); 
    } 

    public function getAllNotification(){
        $notifications = Notification::where('user_id', Auth::user()->id)->orderBy('id','DESC')->get();
        
        $notification['msg'] = "succes";

        foreach($notifications as $notif){
            $notification['notificationList'][] = array(
                'id' => $notif['id'],
                'notification' => $notif['notification'],
                'created_at' => $notif['created_at'],
                );
        }
    
        return response()->json($notification,$this->successStatus);
    }
  
  

   
}

<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\User; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class EditProfileController extends Controller
{
    public $successStatus = 200;

    public function editProfile(Request $request){
        

        $userName = $request->user_name;
        $name = $request->name;
        $files = $request->file('image');
        if(!empty($files)) {
            $extension = $files->getClientOriginalExtension();
            Storage::disk('public')->put($files->getFilename().'.'.$extension,  File::get($files));
    }
        $user = User::find($request->id);
        $user->name = $name;
        $user->user_name = $userName;
        $user->pict = $files->getFileName().'.'.$extension;
        $user->save();

        return response()->json([
            'status' => 'true',
            'msg'=> 'Update profile success'
        ]);
}
}

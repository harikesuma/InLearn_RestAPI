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
        $files = $request->file('imageUpload');

    
    
        if(!empty($files)) {
            // $extension = $files->getClientOriginalExtension();
            // Storage::disk('public')->put($files->getFilename().'.'.$extension,  File::get($files));

            $folderName = 'user';
            $fileName = $userName.'_image';
            $fileExtension = $files->getClientOriginalExtension();
            $fileNameToStorage = $fileName.'_'.time().'.'.$fileExtension;
            $filePath = $files->storeAs('public/'.$folderName , $fileNameToStorage); 
    }
        $user = User::find($request->id);

        Storage::disk('public')->delete("/user/".$user->pict);

        $user->user_name = $userName;
        $user->name = $name;
        $user->pict = $fileNameToStorage;
        $user->save();

        return response()->json([
            'status' => 'true',
            'msg'=> 'Update profile success',
            'name' => $name,
            'user_name' => $userName,
            'pict' => $fileNameToStorage
        ]);
}
}

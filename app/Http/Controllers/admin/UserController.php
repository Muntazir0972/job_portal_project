<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Category;
use App\Models\Job;
use App\Models\Job_Type;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Validator;
use App\Models\SavedJobs;
use Illuminate\Routing\Route;
use PhpParser\Node\Stmt\Echo_;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UserController extends Controller
{
    public function index(){
        $users = User::orderBy('created_at','DESC')->paginate(10);
        return view('admin.users.list',compact('users'));
    }

    public function edit($id){

        $user = User::findOrFail($id);
        return view('admin.users.edit',compact('user'));
    }

    public function updateUser($id,Request $data){
    

        $validator = Validator::make($data->all(),[
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,'.$id.',id'
        ]);

        if ($validator->passes()) {
            
            $user = User::find($id);
            $user->name = $data->name;
            $user->email = $data->email;
            $user->mobile = $data->mobile;
            $user->designation = $data->designation;
            $user->save();

            session()->flash('success','User information Updated Succesfully');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy(Request $data){
        
        $id = $data->id;
        $user = User::find($id);

        if ($user == null) {
            session()->flash('error','User not found');
            return response()->json([
                'status' => false,
            ]);
        }

        $user->delete();
        session()->flash('success','User deleted successfully');
        return response()->json([
            'status' => true,
        ]);
    }
}

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

class JobApplicationController extends Controller
{
    public function index(){

        $applications = JobApplication::orderBy('created_at','ASC')
                                        ->with('job','user','employer')
                                        ->paginate(10);

        return view('admin.job-applications.list',compact('applications'));
    }
    
    public function destroy(Request $data){
        $id = $data->id;

        $jobApplication = JobApplication::find($id);

        if ($jobApplication == null) {
            session()->flash('error','Either job application deleted or not found.');
            return response()->json([
                'status' => false,
            ]);
        }

        $jobApplication->delete();
        $filePath = public_path('resumes/' .$jobApplication->resume_path);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $jobApplication->delete();

        session()->flash('success','Job Application deleted successfully.');
            return response()->json([
                'status' => true,
            ]);
    }
}

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

class JobController extends Controller
{
 public function index(){

    $jobs = Job::orderBy('created_at','DESC')->with('user','applications')->paginate(10);
    return view('admin.jobs.list',compact('jobs'));
 }

 public function edit($id){

   $job = Job::findOrFail($id);

   $categories = Category::orderBy('name','ASC')->get();
   $jobTypes = Job_Type::orderBy('name','ASC')->get();

   return view('admin.jobs.edit',compact('job','categories','jobTypes'));
 }

 public function update(Request $data,$id){

   $rules =[
       'title' => 'required|min:5|max:200',
       'category' => 'required',
       'jobType' => 'required',
       'vacancy' => 'required|integer',
       'location' => 'required|max:50',
       'description' => 'required|min:3|max:200',
       'company_name' => 'required',
   ];

   $validator = Validator::make($data->all(),$rules);

   if ($validator->passes()) {
       
       $job = Job::find($id);
       $job->title = $data->title;
       $job->category_id = $data->category;
       $job->job_type_id = $data->jobType;   
       $job->vacancy = $data->vacancy;
       $job->salary = $data->salary;
       $job->location = $data->location;
       $job->description = $data->description;
       $job->benefits = $data->benefits;
       $job->responsibility = $data->responsibility;
       $job->qualifications = $data->qualifications;
       $job->keywords = $data->keywords;
       $job->experience = $data->experience;
       $job->company_name = $data->company_name;
       $job->company_location = $data->company_location;
       $job->company_website = $data->company_website;

       $job->status = $data->status;
       $job->isFeatured = (!empty($data->isFeatured)) ? $data->isFeatured : 0;
       $job->save();

       session()->flash('success','Job Updated Succesfully.');

       return response()->json([
           'status' => true,
           'errors' => []
       ]);

   } else {
       return response()->json([
           'status' => false,
           'errors' => $validator->errors()
       ]);
   }

}

public function destroy(Request $data){
   $id = $data->id;

   $job = Job::find($id);

   if ($job == null) {
      session()->flash('error','Either job deleted or not found');
      return response()->json([
         'status'=>false,
      ]);
   }

   $job->delete();
   session()->flash('success','Job deleted successfully');
   return response()->json([
      'status'=>true,
   ]);
}


}

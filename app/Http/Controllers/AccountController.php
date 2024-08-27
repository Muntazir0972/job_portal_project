<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Category;
use App\Models\Job;
use App\Models\Job_Type;
use App\Models\JobApplication;
use App\Models\SavedJobs;
use Illuminate\Routing\Route;
use PhpParser\Node\Stmt\Echo_;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class AccountController extends Controller
{
    public function registration(){
        return view('front.account.registration');
    }

    public function processRegistration(Request $data){
        
        $validator = Validator::make($data->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        if ($validator->passes()) {
            
            $user = new User();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->save();

            session()->flash('success','You have registered successfully.');

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

    public function login(){
        return view('front.account.login');
    }

    public function authenticate(Request $data){

        $validator = Validator::make($data->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->passes()) {

            if (Auth::attempt(['email' => $data->email, 'password' => $data->password])) {
                
                return redirect()->route('home');

            } else {
                return redirect()->route('account.login')->with('error','Either Email/Password is incorrect');
            }


        } else {
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($data->only('email'));
        }

    }

    public function profile(){

        $id = Auth::user()->id;
        $user = User::where('id',$id)->first();
        return view('front.account.profile',compact('user'));
    }

    public function updateProfile(Request $data){

        $id = Auth::user()->id;

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

            session()->flash('success','Profile Updated Succesfully');

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

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $data){

        $id = Auth::user()->id;

        $validator = Validator::make($data->all(),[
            'image' => 'required|image',
        ]);

        if ($validator->passes()) {
            
            $image = $data->file('image');
            $ext = $image ->getClientOriginalExtension();
            $imageName = $id.'-'.time().'.'.$ext;   
            $image->move(public_path('/profile_pic/'),$imageName);



            //To create a small thumbnail
            $sourcePath = public_path('/profile_pic/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);
            
            $image->cover(150,150); 
            $image->toPng()->save(public_path('/profile_pic/thumb/'.$imageName));

            //Delete old profile pic
            File::delete(public_path('/profile_pic/thumb/'.Auth::user()->image));
            File::delete(public_path('/profile_pic/'.Auth::user()->image));


        User::where('id',$id)->update(['image' => $imageName]);

        session()->flash('success','Profile Picture Updated Successfully');

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

    public function createJob(){

        $categories = Category::orderBy('name','ASC')->where('status',1)->get();

        $jobTypes = Job_Type::orderBy('name','ASC')->where('status',1)->get();

        return view('front.account.job.create',compact('categories','jobTypes'));
    }

    public function saveJob(Request $data){

        $rules =[
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required|min:3|max:75',
            'company_name' => 'required',
        ];

        $validator = Validator::make($data->all(),$rules);

        if ($validator->passes()) {
            
            $job = new Job();
            $job->title = $data->title;
            $job->category_id = $data->category;
            $job->job_type_id = $data->jobType;
            $job->user_id = Auth::user()->id;
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
            $job->save();

            session()->flash('success','Job Added Succesfully.');

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

    public function myJob(){

        $jobs = Job::where('user_id',Auth::user()->id)->with('jobType')->orderBy('created_at','DESC')->paginate(10);
        return view('front.account.job.my-jobs',compact('jobs'));
    }

    public function editJob(Request $data,$id){

        $categories = Category::orderBy('name','ASC')->where('status',1)->get();
        $jobTypes = Job_Type::orderBy('name','ASC')->where('status',1)->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if ($job == null) {
            abort(404);
        }
        

        return view('front.account.job.edit',compact('categories','jobTypes','job'));
    }

    public function updateJob(Request $data,$id){

        $rules =[
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required|min:3|max:75',
            'company_name' => 'required',
        ];

        $validator = Validator::make($data->all(),$rules);

        if ($validator->passes()) {
            
            $job = Job::find($id);
            $job->title = $data->title;
            $job->category_id = $data->category;
            $job->job_type_id = $data->jobType;
            $job->user_id = Auth::user()->id;
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

    public function deleteJob(Request $data){

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $data->jobId
        ])->first();

        if ($job == null) { 
            session()->flash('error','Either Job Deleted or not found.');
            return response()->json([
                'status' => true,
            ]);

        }
            Job::where('id',$data->jobId)->delete();
            session()->flash('success','Job Deleted Successfully.');
            return response()->json([
                'status' => true,
            ]);
    
    }

    public function myJobApplications(){

       $jobApplications =  JobApplication::where('user_id',Auth::user()->id)
       ->with('job','job.jobType','job.applications')
       ->orderBy('created_at','DESC')
       ->paginate(10);

        return view('front.account.job.my-job-applications',compact('jobApplications'));
    }   

    public function removeJobs(Request $data){

       $jobApplication =  JobApplication::where([
                        'id' => $data->id,
                        'user_id' => Auth::user()->id])
                        ->first(); 

        if ($jobApplication == null) {
            session()->flash('error','Job Application not found');
            return response()->json([
                'status' => false,
            ]);
        }
        
     JobApplication::find($data->id)->delete();
     session()->flash('success','Job application removed successfully');
            return response()->json([
                'status' => true,
            ]);
        
    }

    public function savedJobs(){

        // $jobApplications =  JobApplication::where('user_id',Auth::user()->id)->with('job','job.jobType','job.applications')->paginate(10);

        $savedJobs = SavedJobs::where([
            'user_id' => Auth::user()->id
        ])->with('job','job.jobType','job.applications')
        ->orderBy('created_at','DESC')
        ->paginate(10);

        return view('front.account.job.saved-jobs',compact('savedJobs'));
    }

    public function removeSavedJob(Request $data){

        $savedJob =  SavedJobs::where([
                         'id' => $data->id,
                         'user_id' => Auth::user()->id])
                         ->first(); 
 
         if ($savedJob == null) {
             session()->flash('error','Job not found');
             return response()->json([
                 'status' => false,
             ]);
         }
         
         SavedJobs::find($data->id)->delete();
      session()->flash('success','Job removed successfully');
             return response()->json([
                 'status' => true,
             ]);
         
     }

     public function updatePassword(Request $data){

        $validator = Validator::make($data->all(),[
            'old_password' => 'required',
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),

            ]);
        }

        if (Hash::check($data->old_password, Auth::user()->password) == false) {

            session()->flash('error','Your old password is incorrect.');
            return response()->json([
                'status' => true,
            ]);
        }

        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($data->new_password);
        $user->save();

        session()->flash('success','Password updated successfully.');
            return response()->json([
                'status' => true,
            ]);
     }

    public function forgotPassword(){
        return view('front.account.forgot-password');
    }

    public function processForgotPassword(Request $data){

        $validator = Validator::make($data->all(),[
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.forgotPassword')->withInput()->withErrors($validator);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email',$data->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $data->email,
            'token' => $token,
            'created_at' => now()
        ]);

        //send email here
        $user = User::where('email',$data->email)->first();

        $mailData = [
            'token' => $token,
            'user' => $user,
            'subject' => 'You have requested to change your password'
        ];

        Mail::to($data->email)->send(new ResetPasswordEmail($mailData));

        return redirect()->route('account.forgotPassword')->with('success','Reset password email has been sent to your inbox.');
    }

    public function resetPassword($tokenString){

        $token = DB::table('password_reset_tokens')->where('token',$tokenString)->first();

        if ($token == null) {

            return redirect()->route('account.forgotPassword')->with('error','Invalid token.'); 
        }

        return view('front.account.reset-password',compact('tokenString'));
    }

    public function processResetPassword(Request $data){

        $token = DB::table('password_reset_tokens')->where('token',$data->token)->first();

        if ($token == null) {

            return redirect()->route('account.forgotPassword')->with('error','Invalid token.'); 
        }

        $validator = Validator::make($data->all(),[
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.resetPassword',$data->token)->withErrors($validator);
        }   

        User::where('email',$token->email)->update([
            'password' => Hash::make($data->new_password)
        ]);

        return redirect()->route('account.login')->with('success','You have successfully changed your password.');
    }
}

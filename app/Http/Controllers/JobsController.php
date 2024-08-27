<?php

namespace App\Http\Controllers;
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
use Illuminate\Routing\Route;
use PhpParser\Node\Stmt\Echo_;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobNotificationEmail;
use Illuminate\Support\Facades\Log;
use App\Models\SavedJobs;

class JobsController extends Controller
{
    public function index(Request $data){

        $categories = Category::where('status',1)->get();
        $jobTypes = Job_Type::where('status',1)->get();

        $jobs = Job::where('status',1);

        //search using keyword
        if (!empty($data->keyword)) {
            
            $jobs = $jobs->where(function($query) use ($data){
                $query->orWhere('title','like','%'.$data->keyword.'%');
                $query->orWhere('keywords','like','%'.$data->keyword.'%');
                
            });
        }

        //search using location
        if (!empty($data->location)) {
            $jobs = $jobs->where('location',$data->location);
        }

        //search =>  using location
        if (!empty($data->category)) {
                $jobs = $jobs->where('category_id',$data->category);
        }

        $jobTypeArray =[];
        //search using job type
        if (!empty($data->jobType)) {
            //1,2,3
            $jobTypeArray = explode(',',$data->jobType);
            $jobs = $jobs->whereIn('job_type_id',$jobTypeArray);
        }
     
        //search using experience
        if (!empty($data->experience)) {
            $jobs = $jobs->where('experience',$data->experience);
        }


        $jobs = $jobs->with(['jobType','category']);

        if ($data->sort == '0') {

            $jobs = $jobs->orderBy('created_at','ASC');
            
        }else{
            $jobs = $jobs->orderBy('created_at','DESC');
        }

        $jobs = $jobs ->paginate(9);

        return view('front.jobs',compact('categories','jobTypes','jobs','jobTypeArray'));
    }

    public function detail($id){

        $job = Job::where([
                            'id' => $id ,
                            'status' => 1])->with(['jobType','category'])->first();
        if ($job == null) {
            abort(404);
        }


        $count = 0;
        if (Auth::user()) {            
            //check if user already saved job
            $count = SavedJobs::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id, 
            ])->count();
        }

        //fetch applicants

        $applications = JobApplication::where('job_id',$id)->with('user')->get();
                            
        return view('front.jobDetail',compact('job','count','applications'));
    }

    public function applyJob(Request $data)
    {
        $rules = [
            'applicant_name' => 'required',
            'applicant_email' => 'required|email',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048' // Adjust file validation as needed
        ];
    
        $validator = Validator::make($data->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }
    
        // Check if the job exists
        $id = $data->id;
        $job = Job::find($id);
    
        if (!$job) {
            return redirect()->back()->with('error', 'Job does not exist');
        }
    
        $employer_id = $job->user_id;
    
        // Prevent users from applying to their own job
        if ($employer_id == Auth::id()) {
            return redirect()->back()->with('error', 'You cannot apply to your own job.');
        }
    
        // Check if the user has already applied
        $jobApplicationCount = JobApplication::where('user_id', Auth::id())
            ->where('job_id', $id)
            ->count();
    
        if ($jobApplicationCount > 0) {
            return redirect()->back()->with('error', 'You have already applied for this job.');
        }
    
        // Save the application
        $application = new JobApplication();
        $application->job_id = $id;
        $application->user_id = Auth::id();
        $application->employer_id = $employer_id;
        $application->applied_date = now();
        $application-> applicant_name= $data->applicant_name;
        $application-> applicant_email= $data->applicant_email;
        $application-> cover_letter= $data->cover_letter;
        $application-> expected_salary= $data->expected_salary;
    
        // Upload file if present
        if (!empty($data->resume)) {
            $file = $data->resume;
            $fileName = $file->getClientOriginalName();
            $file->move(public_path('resumes'), $fileName);
            $application->resume_path = $fileName;
            $application->save();
        }
    
        $application->save();
    
        // Send notification email to employer
        $employer = User::find($employer_id);
        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'job' => $job,
        ];
    
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));
    
        return redirect()->back()->with('success', 'You have successfully applied.');
    }
    
    

     public function saveJob(Request $data){

        $id = $data->id;
        $job = Job::find($id);

        if ($job == null) {
            session()->flash('error','Job not found');

            return response()->json([
                'status' => false,
            ]);
        }

        //check if user already saved job
        $count = SavedJobs::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id, 
        ])->count();

        if ($count > 0) {
            session()->flash('error','You have already saved this job.');

            return response()->json([
                'status' => false,
            ]);
        }

        $savedJob = new SavedJobs();
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save(); 

        session()->flash('success','Job saved');

        return response()->json([
            'status' => true,
        ]);
    }

}
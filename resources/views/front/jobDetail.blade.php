@extends('front.layouts.app')

@section('main')

<section class="section-4 bg-2">
    <div class="container pt-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('jobs') }}"><i class="fa fa-arrow-left"
                                    aria-hidden="true"></i> &nbsp;Back to Jobs</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="container job_details_area">
        <div class="row pb-5">
            <div class="col-md-8">
                @include('front.layouts.message')
                <div class="card shadow border-0">
                    <div class="job_details_header">
                        <div class="single_jobs white-bg d-flex justify-content-between">
                            <div class="jobs_left d-flex align-items-center">

                                <div class="jobs_conetent">
                                    <a href="#">
                                        <h4>{{ $job->title }}</h4>
                                    </a>
                                    <div class="links_locat d-flex align-items-center">
                                        <div class="location">
                                            <p> <i class="fa fa-map-marker"></i> {{ $job->location }}</p>
                                        </div>
                                        <div class="location">
                                            <p> <i class="fa fa-clock-o"></i> {{ $job->jobType->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="jobs_right">
                                <div class="apply_now {{ ($count == 1) ? 'saved-job' : '' }}">
                                    <a class="heart_mark" href="javascript:void(0);" onclick="saveJob({{ $job->id }})"> <i class="fa fa-heart-o" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="descript_wrap white-bg">
                        <div class="single_wrap">
                            <h4>Job description</h4>
                            <p>{!! nl2br($job->description) !!}</p>
                        </div>

                        @if (!empty($job->responsibility))

                        <div class="single_wrap">
                            <h4>Responsibility</h4>
                            <ul>
                                <li>{!! nl2br($job->responsibility) !!}</li>
                            </ul>
                        </div>

                        @endif


                        @if (!empty($job->qualifications))

                        <div class="single_wrap">
                            <h4>Qualifications</h4>
                            <ul>
                                <li>{!! nl2br($job->qualifications) !!}</li>
                            </ul>
                        </div>

                        @endif


                        @if (!empty($job->benefits))

                        <div class="single_wrap">
                            <h4>Benefits</h4>
                            <p>{!! nl2br($job->benefits) !!}</p>
                        </div>

                        @endif



                        <div class="border-bottom"></div>
                        <div class="pt-3 text-end">

                            @if (Auth::check())
                            <a href="#" onclick="saveJob({{ $job->id }});" class="btn btn-secondary">Save</a>
                            @else
                            <a href="javascript:void(0);" class="btn btn-secondary disabled">Login to Save</a>
                            @endif


                            @if (Auth::check())
                            <a href="javascript:void(0);" onclick="applyJob({{ $job->id }})" class="btn btn-primary">Apply</a>
                            @else
                            <a href="javascript:void(0);" class="btn btn-primary disabled">Login to Apply</a>
                            @endif

                        </div>
                    </div>
                </div>


                <div id="applicationPopup" class="popup" >
                    <div class="popup-content">
                        <span class="close" style="cursor: pointer" onclick="closePopup()">&times;</span>
                        <h4>Job Application Form</h4>
                        <form id="applicationForm" action="{{ route('applyJob') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="jobId" name="id" value="{{ old('id') }}">
                        
                            <div>
                                <label for="name">Name:</label>
                                <input type="text" class="form-control @error('applicant_name') is-invalid @enderror" id="applicant_name" name="applicant_name" value="{{ old('applicant_name') }}" required>
                                @error('applicant_name')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        
                            <div>
                                <label for="email">Email:</label>
                                <input type="email" class="form-control @error('applicant_email') is-invalid @enderror" id="applicant_email" name="applicant_email" value="{{ old('applicant_email') }}" required>
                                @error('applicant_email')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        
                            <div>
                                <label for="expected_salary">Expected Salary:</label>
                                <input type="text" class="form-control @error('expected_salary') is-invalid @enderror" id="expected_salary" name="expected_salary" value="{{ old('expected_salary') }}">
                                @error('expected_salary')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        
                            <div>
                                <label for="cover_letter">Cover Letter:</label>
                                <textarea name="cover_letter" class="form-control @error('cover_letter') is-invalid @enderror" id="cover_letter" cols="30" rows="10">{{ old('cover_letter') }}</textarea>
                                @error('cover_letter')
                                <p class="text-danger">{{ $message }}</p>
                               @enderror
                            </div>
                        
                            <div>
                                <label for="resume">Resume: (pdf/doc/docx)</label>
                                <input type="file" class="form-control" id="resume" name="resume" required>
                                @error('resume')
                                <p class="text-danger">{{ $message }}</p>
                               @enderror
                            </div>
                        
                            <button type="submit" class="form-control">Submit Application</button>
                        </form>
                        
                    </div>
                </div>
                

                @if (Auth::user())
                    @if (Auth::user()->id == $job->user_id)
                        
                    
                <div class="card shadow border-0 mt-4">
                    <div class="job_details_header">
                        <div class="single_jobs white-bg d-flex justify-content-between">
                            <div class="jobs_left d-flex align-items-center">

                                <div class="jobs_conetent">
                                    <a href="#">
                                        <h4>Applicants</h4>
                                    </a>
                                    
                                </div>
                            </div>
                            <div class="jobs_right">
                                
                            </div>
                        </div>
                    </div>
                    <div class="descript_wrap white-bg">
                    <table class="table table-striped">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Applied Date</th>
                            <th>Applicant Resume</th>
                        </tr>    

                        @if ($applications->isNotEmpty())
                            @foreach ($applications as $application)
                                
                            <tr>
                                <td>{{ $application->user->name }}</td>
                                <td>{{ $application->user->email }}</td>
                                <td>{{ $application->user->mobile }}</td>
                                <td>{{ \Carbon\Carbon::parse($application->applied_date)->format('d M,Y') }}</td>
                                
                                
                                <td>
                                    @if ($application->resume_path)
                                    <a href="{{ asset('resumes/' . $application->resume_path) }}" target="_blank" class="btn btn-info btn-sm" download>Download Resume</a>
                                    @else
                                    No Resume
                                    @endif
                                </td>
                            </tr>
                        
                            @endforeach
                            @else
                            <tr>
                                <td colspan="3">Applicants not found</td>
                            </tr>
                        @endif

                    </table>  
                    </div>
                </div>
                @endif

                @endif

            </div>
            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="job_sumary">
                        <div class="summery_header pb-1 pt-4">
                            <h3>Job Summery</h3>
                        </div>
                        <div class="job_content pt-3">
                            <ul>
                                <li>Published on: <span>{{ ($job->created_at)->format('d M, Y') }}</span></li>
                                <li>Vacancy: <span>{{ $job->vacancy }}</span></li>

                                @if (!empty($job->salary))
                                <li>Salary: <span>{{ $job->salary }}</span></li>
                                @endif

                                <li>Location: <span>{{ $job->location }}</span></li>
                                <li>Job Nature: <span> {{ $job->jobType->name }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card shadow border-0 my-4">
                    <div class="job_sumary">
                        <div class="summery_header pb-1 pt-4">
                            <h3>Company Details</h3>
                        </div>
                        <div class="job_content pt-3">
                            <ul>
                                <li>Name: <span>{{ $job->company_name }}</span></li>

                                @if (!empty($job->company_location))
                                <li>Locaion: <span>{{ $job->company_location }}</span></li>
                                @endif

                                @if (!empty($job->company_website))
                                <li>Webite: <span><a href="{{ $job->company_website }}">{{ $job->company_website}}</a></span></li>
                                @endif

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('customJs')
    <script type="text/javascript">
            function applyJob(id){

            if (confirm("Are you sure you want to apply on this job?")) {   
                $.ajax({
                    url:'{{ route("applyJob") }}',
                    type:'post',
                    data:{id:id},
                    dataType: 'json',
                    success:function (response){
                        window.location.href="{{ url()->current() }}";
                    }
                });
            }
        }

            


    function openPopup(id) {
    var popup = document.getElementById('applicationPopup');
    document.getElementById('jobId').value = id;
    popup.style.display = 'block';
    
    void popup.offsetWidth;
    
    popup.classList.add('show');

    // function applyJob(id){
    
    // $.ajax({
    //     url:'{{ route("applyJob") }}',
    //     type:'post',
    //     data:{id:id},
    //     dataType: 'json',
    //     success:function (response){
    //         window.location.href="{{ url()->current() }}";
    //     },
    // });
    // }
   
}

function closePopup() {
    var popup = document.getElementById('applicationPopup');
    popup.classList.remove('show');
    

    setTimeout(function() {
        popup.style.display = 'none';
    }, 300);
}


function applyJob(id) {
    openPopup(id);
}

// $(document).ready(function() {
//     $('#applicationForm').on('submit', function(e) {
//         e.preventDefault();

//         var formData = new FormData(this);

//         closePopup();

//         $.ajax({
//             url: '{{ route("applyJob") }}',
//             type: 'POST',
//             data: formData,
//             processData: false,
//             contentType: false,
//             dataType: 'json',
//             headers: {
//                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
//             },
//             success: function(response) {
//                 if (response.status) {
                    
//                     setTimeout(function() {
//                         window.location.href = "{{ url()->current() }}";
//                     }, 300);
//                 } else {
//                     alert(response.message || 'Error submitting application. Please try again.');
//                 }
//             },
//             error: function(xhr, status, error) {
//                 console.error('Error:', error);
//                 alert('An error occurred. Please try again.');
//             }
//         });
//     });
// });



    function saveJob(id){

        $.ajax({
            url:'{{ route("saveJob") }}',
            type:'post',
            data:{id:id},
            dataType: 'json',
            success:function (response){
                window.location.href="{{ url()->current() }}";
            }
        });
      
    }


    </script>
@endsection
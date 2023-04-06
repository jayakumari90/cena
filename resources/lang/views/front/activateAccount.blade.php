<!DOCTYPE html>
<html lang="en">
<head>

    <title>{{env('APP_NAME','FAM')}}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Favicon icon -->
    <link rel="icon" href="{{asset('public/assets/images/favicon.ico')}}" type="image/x-icon">
    
    <!-- animation css -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/animation/css/animate.min.css')}}">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{asset('public/assets/css/style.css')}}">

</head>

<body>

    <!-- [ Main Content ] start -->
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">

                            <div class="col-xl-12">
                                <div class="row">

                                    <div class="col-sm-4 m-auto">
                                        <div class="card text-white bg-{{$type}} ">
                                            <div class="card-body text-center p-5">
                                                <h5 class="card-title text-white mb-4">{{$title}} !!</h5>
                                                <p class="card-text">{{$message}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ Main Content ] end -->
                    </div>
                </div>
            </div>
        </div>
    <!-- Required Js -->
</body>

</html>
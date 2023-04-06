<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }}</title>

    <!-- HTML5 Shim and Respond.js IE11 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 11]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="{{asset('public/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
      <link rel="stylesheet" href="{{asset('public/assets/vendors/css/vendor.bundle.base.css') }}">
      <!-- endinject -->
      <!-- plugin css for this page -->
      <!-- End plugin css for this page -->
      <!-- inject:css -->   
      <link rel="stylesheet" href="{{asset('public/assets/css/style.css') }}">
      <!-- endinject -->
      <link rel="shortcut icon" href="{{asset('public/assets/images/favicon.png') }}" />
</head>
<body>
    
            @yield('content')
        
<!-- container-scroller -->
  <!-- base:js -->
  <script src="{{asset('public/assets/vendors/js/vendor.bundle.base.js') }}"></script>
  <!-- endinject -->
  <!-- inject:js -->
  <script src="{{asset('public/assets/js/off-canvas.js') }}"></script>
  <script src="{{asset('public/assets/js/hoverable-collapse.js') }}"></script>
  <script src="{{asset('public/assets/js/template.js') }}"></script>
  <!-- endinject -->
</body>
</html>

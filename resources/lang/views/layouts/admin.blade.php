<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

  <meta charset="utf-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title><?php echo env('APP_NAME')?></title>
  <!-- base:css -->
  <link rel="stylesheet" href="{{asset('public/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{asset('public/assets/vendors/css/vendor.bundle.base.css') }}">
  <!-- endinject -->
  <!-- plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="{{asset('public/assets/css/style.css') }}">
  <!-- endinject -->
  <link rel="shortcut icon" href="{{asset('public/assets/images/favicon.png') }} " />
  <!-- vendor css -->
  <link rel="stylesheet" href="{{asset('public/assets/css/style.css')}}" />
<link rel="stylesheet" href="http://cdn.datatables.net/1.10.18/css/jquery.dataTables.min.css">
 
  @stack('styles')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>

<body>
  <div class="container-scroller d-flex">
     @include('admin.includes.aside')
    <div class="container-fluid page-body-wrapper">
      @include('admin.includes.header')
      @yield('content')
    </div> 
  </div>
<!-- base:js -->
  <script src="{{asset('public/assets/vendors/js/vendor.bundle.base.js') }}"></script>
  <!-- endinject -->
  <!-- Plugin js for this page-->
  <script src="{{asset('public/assets/vendors/chart.js/Chart.min.js') }}"></script>
  <!-- End plugin js for this page-->
  <!-- inject:js -->
  <script src="{{asset('public/assets/js/off-canvas.js') }}"></script>
  <script src="{{asset('public/assets/js/hoverable-collapse.js') }}"></script>
  <script src="{{asset('public/assets/js/template.js') }}"></script>
  <!-- endinject -->
  <!-- plugin js for this page -->
  <!-- End plugin js for this page -->
  <!-- Custom js for this page-->
  <script src="{{asset('public/assets/js/dashboard.js') }}"></script>
  <!-- End custom js for this page-->
</body>

</html>
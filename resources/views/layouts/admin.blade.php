<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @include('admin.includes.style')
 </head>

<body>
  <div class="container-scroller d-flex">
     @include('admin.includes.aside')
    <div class="container-fluid page-body-wrapper">
      @include('admin.includes.header')
      @include('admin.includes.breadcum')
      @yield('content')
    </div> 
  </div>
  @include('admin.includes.script')
</body>

</html>
<style>
.sidebar-container {
    text-align: center;
    background: rgb(211,91,15);
    height: 59px;
    text-align: center;
    padding: 11px;
}
.sidebar-container a h3{
  color: #fff;
}
</style>
<!-- partial:./partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="container sidebar-container"><a class="navbar-brand" href="#"><h3 >CENA</h3></a></div>
      <ul class="nav">
      
         <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.home') }}">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Dashboard</span>
<!--             <div class="badge badge-info badge-pill">2</div>
 -->          </a>
        </li>

        
        <li class="nav-item">
          <a class="nav-link" href="{{route('admin.user')}}">
            <i class="mdi mdi-account-multiple menu-icon"></i>
            <span class="menu-title">Users</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('appPage')}}">
            <i class="mdi mdi-file menu-icon"></i>
            <span class="menu-title">App Pages</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
            <i class="mdi mdi-palette menu-icon"></i>
            <span class="menu-title">Restaurant</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="ui-basic">
            <ul class="nav flex-column sub-menu">
              <li class="nav-item"> <a class="nav-link" href="{{route('restaurant_cat')}}">Restaurant Category</a></li>
              <li class="nav-item"> <a class="nav-link" href="{{route('admin.restaurant')}}">Restaurant List</a></li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('struggling')}}">
            <i class="mdi mdi-file menu-icon"></i>
            <span class="menu-title">Struggling</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#ui-basic1" aria-expanded="false" aria-controls="ui-basic">
            <i class="mdi mdi-palette menu-icon"></i>
            <span class="menu-title">Dish</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="ui-basic1">
            <ul class="nav flex-column sub-menu">
              <li class="nav-item"> <a class="nav-link" href="{{route('dish_cat')}}">Dish Category</a></li>
              <li class="nav-item"> <a class="nav-link" href="{{route('admin.dish')}}">Dish List</a></li>
            </ul>
          </div>
        </li>
         <li class="nav-item">
          <a class="nav-link" href="{{route('order')}}">
            <i class="mdi mdi-file menu-icon"></i>
            <span class="menu-title">Orders</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{route('reported.users.index')}}">
            <i class="mdi mdi-file menu-icon"></i>
            <span class="menu-title">Reported Users</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{route('plan')}}">
            <i class="mdi mdi-file menu-icon"></i>
            <span class="menu-title">Subscription</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('settings')}}">
            <i class="mdi mdi-settings menu-icon"></i>
            <span class="menu-title">Settings</span>
          </a>
        </li>
      </ul>
    </nav>
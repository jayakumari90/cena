<!-- partial:./partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
      <ul class="nav">
        <li class="nav-item sidebar-category">
          <p>Navigation</p>
          <span></span>
        </li>
         <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.home') }}">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Dashboard</span>
<!--             <div class="badge badge-info badge-pill">2</div>
 -->          </a>
        </li>
        <!--<li class="nav-item sidebar-category">
          <p>Components</p>
          <span></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
            <i class="mdi mdi-palette menu-icon"></i>
            <span class="menu-title">UI Elements</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="ui-basic">
            <ul class="nav flex-column sub-menu">
              <li class="nav-item"> <a class="nav-link" href="{{route('admin.user')}}">Users</a></li>
            </ul>
          </div>
        </li><i class="mdi mdi-view-headline menu-icon"></i> -->
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
          <a class="nav-link" href="{{route('subscription')}}">
            <i class="mdi mdi-crown-outline menu-icon"></i>
            <span class="menu-title">In-App Subscription</span>
          </a>
        </li>
      </ul>
    </nav>
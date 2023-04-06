<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-6 d-flex flex-row">
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button>
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                @if(Auth::user()->profile_image)
                <img src="{{asset(Auth::user()->profile_image)}}" class="img-radius shadow" alt="User-Profile-Image" style="height:30px;" />
                @else
                <img src="{{asset('public/uploads/default-avatar.svg')}}" class="img-radius shadow" alt="User-Profile-Image" style="height:30px;" />
                @endif
                <span class="nav-profile-name">{{Auth::user()->name}}</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                <a class="dropdown-item" href="{{route('admin.editprofile')}}">
                  <i class="mdi mdi-face-profile text-primary"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="{{route('admin.changepassword')}}">
                  <i class="mdi mdi-settings text-primary"></i>
                  Change Password
                </a>
                <a class="dropdown-item" href="{{ route('admin.logout') }}">
                  <i class="mdi mdi-logout text-primary"></i>
                  Logout
                </a>
              </div>
            </li>
          </ul>
        </div>
        
      </nav>
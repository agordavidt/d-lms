<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | G-Luper LMS</title>
    
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.png') }}">
    <link href="{{ asset('assets/plugins/pg-calendar/css/pignose.calendar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    @stack('styles')
</head>

<body>
    <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
            </svg>
        </div>
    </div>

    <div id="main-wrapper">
        <!-- Nav Header -->
        <div class="nav-header">
            <div class="brand-logo">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isMentor() ? route('mentor.dashboard') : route('learner.dashboard')) }}">
                    <b class="logo-abbr">
                        <span style="font-size: 24px; font-weight: 900; color: #7571f9;">G</span>
                    </b>
                    <span class="brand-title">
                        <span style="font-size: 20px; font-weight: 700; color: #333;">Luper LMS</span>
                    </span>
                </a>
            </div>
        </div>

        <!-- Header -->
        <div class="header">    
            <div class="header-content clearfix">
                <div class="nav-control">
                    <div class="hamburger">
                        <span class="toggle-icon"><i class="icon-menu"></i></span>
                    </div>
                </div>
                
                <div class="header-left">
                    <div class="input-group icons">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 pr-2 pr-sm-3"><i class="mdi mdi-magnify"></i></span>
                        </div>
                        <input type="search" class="form-control" placeholder="Search Dashboard">
                    </div>
                </div>
                
                <div class="header-right">
                    <ul class="clearfix">
                        <!-- Notifications -->
                        <li class="icons dropdown">
                            <a href="javascript:void(0)" data-toggle="dropdown">
                                <i class="mdi mdi-bell"></i>
                                <span class="badge badge-pill gradient-2">3</span>
                            </a>
                            <div class="drop-down animated fadeIn dropdown-menu dropdown-notfication">
                                <div class="dropdown-content-heading d-flex justify-content-between">
                                    <span>3 New Notifications</span>  
                                </div>
                                <div class="dropdown-content-body">
                                    <ul>
                                        <li>
                                            <a href="javascript:void()">
                                                <span class="mr-3 avatar-icon bg-success-lighten-2"><i class="icon-calendar"></i></span>
                                                <div class="notification-content">
                                                    <h6 class="notification-heading">Upcoming Session</h6>
                                                    <span class="notification-text">You have a session in 2 hours</span> 
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <!-- User Profile -->
                        <li class="icons dropdown">
                            <div class="user-img c-pointer position-relative" data-toggle="dropdown">
                                <span class="activity active"></span>
                                <img src="{{ auth()->user()->avatar_url }}" height="40" width="40" alt="" style="border-radius: 50%;">
                            </div>
                            <div class="drop-down dropdown-profile dropdown-menu">
                                <div class="dropdown-content-body">
                                    <ul>
                                        <li>
                                            <a href="javascript:void()">
                                                <i class="icon-user"></i> 
                                                <span>{{ auth()->user()->name }}</span>
                                            </a>
                                        </li>
                                       
                                        <hr class="my-2">
                                        
                                        <li>
                                            <form action="{{ route('logout') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="icon-key"></i> 
                                                    <span>Logout</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="nk-sidebar">           
            <div class="nk-nav-scroll">
                <ul class="metismenu" id="menu">
                    <li class="nav-label">Main Menu</li>
                    
                    @if(auth()->user()->isAdmin())
                    <!-- Admin Menu -->
                    <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}">
                            <i class="icon-people menu-icon"></i><span class="nav-text">Users</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.programs.index') }}">
                            <i class="icon-book-open menu-icon"></i><span class="nav-text">Programs</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('admin.cohorts.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.cohorts.index') }}">
                            <i class="icon-layers menu-icon"></i><span class="nav-text">Cohorts</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.sessions.calendar') }}">
                            <i class="icon-calendar menu-icon"></i><span class="nav-text">Sessions</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="#">
                            <i class="icon-credit-card menu-icon"></i><span class="nav-text">Payments</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('admin.activity-log*') ? 'active' : '' }}">
                        <a href="{{ route('admin.activity-log') }}">
                            <i class="icon-docs menu-icon"></i><span class="nav-text">Activity Log</span>
                        </a>
                    </li>
                    @endif
                    
                    @if(auth()->user()->isMentor())
                    <!-- Mentor Menu -->
                    <li class="{{ request()->routeIs('mentor.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('mentor.dashboard') }}">
                            <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('mentor.sessions.*') ? 'active' : '' }}">
                        <a href="{{ route('mentor.sessions.calendar') }}">
                            <i class="icon-calendar menu-icon"></i><span class="nav-text">My Classes</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('mentor.students.*') ? 'active' : '' }}">
                        <a href="{{ route('mentor.students.index') }}">
                            <i class="icon-people menu-icon"></i><span class="nav-text">My Students</span>
                        </a>
                    </li>
                    @endif
                    
                    @if(auth()->user()->isLearner())
                    <!-- Learner Menu -->
                    <li class="{{ request()->routeIs('learner.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('learner.dashboard') }}">
                            <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('learner.programs.*') ? 'active' : '' }}">
                        <a href="{{ route('learner.programs.index') }}">
                            <i class="icon-book-open menu-icon"></i><span class="nav-text">Browse Programs</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('learner.calendar') ? 'active' : '' }}">
                        <a href="{{ route('learner.calendar') }}">
                            <i class="icon-calendar menu-icon"></i><span class="nav-text">My Schedule</span>
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('learner.profile.*') ? 'active' : '' }}">
                        <a href="{{ route('learner.profile.edit') }}">
                            <i class="icon-user menu-icon"></i><span class="nav-text">My Profile</span>
                        </a>
                    </li>
                    @endif

                    <li class="nav-label">Support</li>
                    <li>
                        <a href="#">
                            <i class="icon-question menu-icon"></i><span class="nav-text">Help Center</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content Body -->
        <div class="content-body">
            <div class="row page-titles mx-0">
                <div class="col p-md-0">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">@yield('breadcrumb-parent', 'Dashboard')</a></li>
                        <li class="breadcrumb-item active"><a href="javascript:void(0)">@yield('breadcrumb-current', 'Home')</a></li>
                    </ol>
                </div>
            </div>

            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="copyright">
                <p>Copyright &copy; {{ date('Y') }} G-Luper Learning Management System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/common/common.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.min.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/gleek.js') }}"></script>
    <script src="{{ asset('assets/js/styleSwitcher.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        toastr.options = {
            "progressBar": true,
            "positionClass": "toast-top-right",
            "closeButton": true,
            "timeOut": "5000"
        };

        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            switch (type) {
                case 'info': toastr.info("{{ Session::get('message') }}"); break;
                case 'success': toastr.success("{{ Session::get('message') }}"); break;
                case 'warning': toastr.warning("{{ Session::get('message') }}"); break;
                case 'error': toastr.error("{{ Session::get('message') }}"); break;
            }
        @endif
    </script>

    @stack('scripts')
</body>
</html>
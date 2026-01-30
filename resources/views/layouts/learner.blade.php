<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Learning') | G-Luper LMS</title>
    
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.png') }}">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    <style>
        /* Collapsible Sidebar Styles */
        .nk-sidebar {
            width: 250px;
            transition: all 0.3s ease;
        }
        
        .nk-sidebar.collapsed {
            width: 70px;
        }
        
        .nk-sidebar.collapsed .nav-text,
        .nk-sidebar.collapsed .brand-title {
            display: none;
        }
        
        .nk-sidebar.collapsed .brand-logo {
            text-align: center;
            padding: 20px 0;
        }
        
        .content-body {
            margin-left: 250px;
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed .content-body {
            margin-left: 70px;
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 260px;
            z-index: 999;
            width: 40px;
            height: 40px;
            background: #7571f9;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed .sidebar-toggle {
            left: 80px;
        }
        
        .sidebar-toggle:hover {
            background: #5f5bd1;
            transform: scale(1.1);
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .nk-sidebar {
                width: 250px;
                position: fixed;
                left: -250px;
                z-index: 1000;
            }
            
            .nk-sidebar.show {
                left: 0;
            }
            
            .content-body {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                left: 20px;
            }
        }
    </style>
    
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
        <!-- Sidebar Toggle Button -->
        <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <i class="icon-menu"></i>
        </button>

        <!-- Sidebar -->
        <div class="nk-sidebar" id="sidebar">
            <!-- Logo -->
            <div class="nav-header">
                <div class="brand-logo">
                    <a href="{{ route('learner.learning.index') }}">
                        <b class="logo-abbr">
                            <span style="font-size: 24px; font-weight: 900; color: #7571f9;">G</span>
                        </b>
                        <span class="brand-title">
                            <span style="font-size: 20px; font-weight: 700; color: #333;">Luper LMS</span>
                        </span>
                    </a>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="nk-nav-scroll">
                <ul class="metismenu" id="menu">
                    @php
                        $hasActiveEnrollment = auth()->user()->enrollments()->where('status', 'active')->exists();
                    @endphp

                    @if(!$hasActiveEnrollment)
                        <!-- Pre-Enrollment Navigation -->
                        <li class="{{ request()->routeIs('learner.programs.*') ? 'active' : '' }}">
                            <a href="{{ route('learner.programs.index') }}">
                                <i class="icon-book-open menu-icon"></i><span class="nav-text">Programs</span>
                            </a>
                        </li>
                    @else
                        <!-- Post-Enrollment Navigation -->
                        <li class="{{ request()->routeIs('learner.learning.*') ? 'active' : '' }}">
                            <a href="{{ route('learner.learning.index') }}">
                                <i class="icon-graduation menu-icon"></i><span class="nav-text">Learning</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('learner.curriculum') ? 'active' : '' }}">
                            <a href="{{ route('learner.curriculum') }}">
                                <i class="icon-layers menu-icon"></i><span class="nav-text">Curriculum</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('learner.calendar') ? 'active' : '' }}">
                            <a href="{{ route('learner.calendar') }}">
                                <i class="icon-calendar menu-icon"></i><span class="nav-text">Calendar</span>
                            </a>
                        </li>
                    @endif

                    <li class="{{ request()->routeIs('learner.profile.*') ? 'active' : '' }}">
                        <a href="{{ route('learner.profile.edit') }}">
                            <i class="icon-user menu-icon"></i><span class="nav-text">Profile</span>
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="icon-question menu-icon"></i><span class="nav-text">Help</span>
                        </a>
                    </li>
                    
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="icon-logout menu-icon"></i><span class="nav-text">Logout</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-body" style="padding: 0; margin-top: 0;">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('assets/plugins/common/common.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Toastr configuration
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

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainWrapper = document.getElementById('main-wrapper');
            
            sidebar.classList.toggle('collapsed');
            mainWrapper.classList.toggle('sidebar-collapsed');
            
            // Save state
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Restore sidebar state on load
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('main-wrapper').classList.add('sidebar-collapsed');
            }
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar-toggle').addEventListener('click', function(e) {
                e.stopPropagation();
                document.getElementById('sidebar').classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.querySelector('.sidebar-toggle');
                
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
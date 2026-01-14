<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | G-Luper LMS</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.1/css/responsive.bootstrap5.min.css">

    <style>
        :root {
            --bs-primary: #4f46e5;
            --bs-primary-rgb: 79, 70, 229;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-800);
            overflow-x: hidden;
        }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            box-shadow: none;
            transition: all 0.3s;
            z-index: 996;
            overflow-y: auto;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .sidebar-nav .nav-link {
            font-weight: 600;
            color: var(--slate-800);
            padding: 12px 20px;
            border-radius: 12px;
            margin: 5px 15px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav .nav-link:hover, 
        .sidebar-nav .nav-link.active {
            background: var(--slate-100);
            color: var(--bs-primary);
        }

        .sidebar-nav .nav-link i {
            font-size: 1.2rem;
            color: #94a3b8;
        }

        .sidebar-nav .nav-link.active i {
            color: var(--bs-primary);
        }

        /* Main Content */
        #main {
            margin-left: 280px;
            padding: 30px;
            transition: all 0.3s;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: white;
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-title {
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.02em;
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #6366f1 100%);
            color: white;
            border-radius: 20px;
            padding: 25px;
        }

        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 0;
        }

        /* DataTables */
        .dataTables_wrapper .dt-paging .pagination .page-item.active .page-link {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            border-radius: 8px;
        }

        table.dataTable {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #f1f5f9 !important;
        }

        table.dataTable thead {
            background-color: var(--slate-50);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.1em;
            font-weight: 700;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--bs-primary);
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 4px 14px 0 rgba(var(--bs-primary-rgb), 0.39);
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        /* Logo */
        .logo {
            padding: 25px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--bs-primary) 0%, #6366f1 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 20px;
        }

        .logo-text {
            font-size: 22px;
            font-weight: 800;
            color: var(--slate-900);
        }

        /* Profile Dropdown */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 12px;
            transition: 0.2s;
        }

        .user-profile:hover {
            background: var(--slate-50);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }

        /* Badge Styles */
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .footer {
            background: transparent;
            padding: 40px 0;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            margin-top: 60px;
        }

        /* Responsive */
        @media (max-width: 1199px) {
            #sidebar {
                left: -280px;
            }
            #main {
                margin-left: 0;
            }
            #sidebar.show {
                left: 0;
            }
        }

        /* Toggle Button */
        .toggle-sidebar-btn {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 1199px) {
            .toggle-sidebar-btn {
                display: block;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}" class="logo-container">
                <div class="logo-icon">G</div>
                <span class="logo-text">Luper</span>
            </a>
        </div>

        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    <span>User Management</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-book"></i>
                    <span>Programs</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-calendar-event"></i>
                    <span>Cohorts</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                        <span class="text-danger">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main id="main" class="main">
        <!-- Header -->
        <div class="header">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-list toggle-sidebar-btn" onclick="toggleSidebar()"></i>
                <div>
                    <h4 class="mb-0 fw-bold">@yield('page-title', 'Dashboard')</h4>
                    <p class="text-muted mb-0 small">@yield('page-subtitle', 'Welcome back!')</p>
                </div>
            </div>

            <div class="dropdown">
                <div class="user-profile" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="user-avatar">
                    <div class="d-none d-md-block">
                        <div class="fw-bold" style="font-size: 14px;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: 12px;">{{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="container-fluid">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="text-center">
                <div class="fw-bold text-slate-800">
                    &copy; {{ date('Y') }} <span>G-Luper Learning Management System</span>
                </div>
                <div class="text-muted mt-2">
                    Accelerating Digital Excellence
                </div>
            </div>
        </footer>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Toastr Configuration
        toastr.options = {
            "progressBar": true,
            "positionClass": "toast-top-right",
            "closeButton": true,
            "timeOut": "5000"
        };

        // Display notifications
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
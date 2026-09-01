{{-- @include('admin_panel.layout.header') --}}

{{-- @yield('content')
@include('admin_panel.layout.footer') --}}



<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <style>
        /* =========================================================
           ERP Mega Menu & Navbar Responsive Styling
           ========================================================= */
        .rt_nav_header.horizontal-layout .nav-bottom {
            position: relative;
        }

        /* Base Submenu Styling */
        .nav-item .submenu,
        .mega-menu .submenu {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1), 0 2px 6px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f6;
            z-index: 1050;
        }

        .mega-menu .category-heading {
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #f1f5f9;
            text-transform: capitalize;
            letter-spacing: 0.2px;
        }

        .nav-item .submenu-item li,
        .mega-menu .submenu-item li {
            margin-bottom: 3px;
            list-style: none;
        }

        .nav-item .submenu-item li a,
        .mega-menu .submenu-item li a {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            padding: 5px 8px;
            border-radius: 6px;
            transition: all 0.18s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-item .submenu-item li a i,
        .mega-menu .submenu-item li a i {
            font-size: 13px;
            margin-right: 8px;
            color: #3b82f6;
            min-width: 18px;
            text-align: center;
            transition: color 0.18s ease;
        }

        .nav-item .submenu-item li a:hover,
        .mega-menu .submenu-item li a:hover {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            padding-left: 10px;
        }

        .nav-item .submenu-item li a:hover i,
        .mega-menu .submenu-item li a:hover i {
            color: #1d4ed8;
        }

        /* Desktop Mega Menu Positioning */
        @media (min-width: 768px) {
            .rt_nav_header.horizontal-layout .nav-bottom .page-navigation > .nav-item.mega-menu {
                position: static !important;
            }

            .mega-menu .submenu {
                position: absolute !important;
                top: 100% !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: max-content !important;
                max-width: 98vw !important;
                right: auto !important;
                padding: 16px 20px !important;
                border-radius: 10px !important;
                box-shadow: 0 14px 35px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.04) !important;
                border: 1px solid #eef2f6 !important;
            }

            .mega-menu .col-group-wrapper {
                display: flex !important;
                flex-wrap: nowrap !important;
                gap: 0 !important;
                margin: 0 !important;
                align-items: stretch !important;
            }

            .mega-menu .col-group {
                width: 215px !important;
                flex: 0 0 215px !important;
                min-width: 215px !important;
                max-width: 215px !important;
                padding: 0 16px !important;
                border-right: 1px solid #f1f5f9 !important;
                margin: 0 !important;
            }

            .mega-menu .col-group:last-child {
                border-right: none !important;
            }

            /* Regular Submenu Right Alignment for last items to prevent screen overflow */
            .rt_nav_header.horizontal-layout .nav-bottom .page-navigation > .nav-item:nth-last-child(-n+3):not(.mega-menu) .submenu {
                left: auto !important;
                right: 0 !important;
            }
        }

        /* Mobile / Tablet Responsive Navigation */
        @media (max-width: 767.98px) {
            .rt_nav_header.horizontal-layout .nav-bottom.header-toggled {
                display: block;
                max-height: calc(100vh - 70px);
                overflow-y: auto;
                padding: 10px 15px;
                background: #ffffff;
                border-bottom: 2px solid #e2e8f0;
                box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            }

            .mega-menu {
                position: relative !important;
            }

            .mega-menu .submenu {
                position: relative !important;
                top: 0 !important;
                left: 0 !important;
                transform: none !important;
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 6px 12px !important;
                background: #f8fafc !important;
                border-radius: 6px !important;
            }

            .mega-menu .col-group-wrapper {
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                margin: 0 !important;
            }

            .mega-menu .col-group {
                width: 100% !important;
                max-width: 100% !important;
                border-right: none !important;
                border-bottom: 1px solid #e2e8f0 !important;
                padding: 0 0 8px 0 !important;
                margin: 0 !important;
            }

            .mega-menu .col-group:last-child {
                border-bottom: none !important;
            }

            .rt_nav_header.horizontal-layout .nav-bottom .page-navigation > .nav-item {
                display: block;
                width: 100%;
                border-bottom: 1px solid #f1f5f9;
            }

            .rt_nav_header.horizontal-layout .nav-bottom .page-navigation > .nav-item > .nav-link {
                padding: 12px 6px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .rt_nav_header.horizontal-layout .nav-bottom .page-navigation > .nav-item .submenu {
                position: relative;
                top: 0;
                box-shadow: none;
                background: #f8fafc;
                border-radius: 6px;
                padding: 8px 12px;
            }
        }
    </style>
    <!--=========================*
                Met Data
    *===========================-->
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zare Bootstrap 4 Admin Template">

    <!--=========================*
              Page Title
    *===========================-->
    <title>{{ \App\Models\Setting::get('company_name', 'prowave technogies') }}</title>

    <!--=========================*
                Favicon
    *===========================-->

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/themify-icons.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/ionicons.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/et-line.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/feather.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/flag-icon.min.css') }}">
    <script src="{{ asset('assets/js/modernizr-2.8.3.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/metisMenu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slicknav.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/am-charts/css/am-charts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/charts/morris-bundle/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/charts/c3charts/c3.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/jquery.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.jqueryui.min.css') }}">
    {{-- Removed Duplicate External CDN Scripts (BS5/jQuery) to prevent conflicts with Template BS4 --}}
    {{-- Online Links --}}
  
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/brands.min.css') }}">
    {{-- Select2 CSS - Local --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/select2/css/select2.min.css') }}">

    {{-- SweetAlert2 CSS - Local --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/sweetalert2/css/sweetalert2.min.css') }}">

    {{-- Flatpickr CSS for custom date formats --}}
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> -->
    <link rel="stylesheet" href="{{ asset('assets/css/flatpickr.min.css') }}">

    <style>
        .dropdown-toggle::after,
        .nav-link::after,
        .navbar-nav .nav-item .nav-link::after,
        .count-indicator::after {
            display: none !important;
            content: none !important;
        }
        .rt_nav_header.horizontal-layout .top_nav {
            background: #090e1a !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.4) !important;
            height: 66px;
            padding: 0 12px;
        }
        .header-pw-badge {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #0d1527;
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 19px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            flex-shrink: 0;
            margin-right: 12px;
        }
        .header-company-pill {
            background: rgba(30, 58, 138, 0.25);
            border: 1px solid rgba(59, 130, 246, 0.35);
            border-radius: 10px;
            padding: 6px 14px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 20px;
        }
        .header-support-btn {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(59, 130, 246, 0.45);
            border-radius: 10px;
            padding: 6px 14px;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .header-support-btn:hover {
            background: rgba(37, 99, 235, 0.25);
            border-color: rgba(96, 165, 250, 0.6);
            transform: translateY(-1px);
        }
        .header-online-pill {
            background: rgba(6, 78, 59, 0.6);
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 10px;
            padding: 6px 14px;
            color: #34d399;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        /* Mobile Dropdown Fix */
        @media (max-width: 768px) {
            .rt_nav_header.horizontal-layout .top_nav {
                padding: 0 8px !important;
            }
            .top_nav .dropdown-menu {
                position: fixed !important;
                top: 60px !important;
                right: 8px !important;
                left: auto !important;
                min-width: 220px !important;
                max-width: calc(100vw - 16px) !important;
                z-index: 99999 !important;
                transform: none !important;
                box-shadow: 0 10px 35px rgba(0, 0, 0, 0.45) !important;
            }
            .header-pw-badge {
                width: 32px !important;
                height: 32px !important;
                font-size: 16px !important;
                margin-right: 6px !important;
            }
            .header-online-pill {
                padding: 5px 8px !important;
            }
        }

        /* Fix Navigation Overlapping Page Content Universally */
        .rt_nav_header.horizontal-layout {
            position: relative !important;
            z-index: 1020 !important;
            margin-bottom: 0 !important;
            width: 100% !important;
        }
        .rt_nav_header.horizontal-layout .nav-bottom,
        .rt_nav_header.horizontal-layout.fixed-on-scroll .nav-bottom {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            z-index: 1020 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        }

        /* Universal App Page Body - Generous Top Clearance */
        .app-page-body {
            position: relative;
            width: 100%;
            min-height: calc(100vh - 140px);
            padding-top: 55px !important;
            padding-bottom: 60px !important;
            box-sizing: border-box;
        }

        .main-content {
            position: relative !important;
            width: 100% !important;
            padding-top: 15px !important;
            padding-bottom: 40px !important;
            min-height: auto !important;
        }
        .main-content-inner {
            padding: 10px 0 30px 0 !important;
        }
        .sale-report-container,
        .report-page-container,
        .cat-page,
        .brand-page,
        .erp-page {
            padding-top: 15px !important;
        }
        .app-page-body > .card:first-child,
        .app-page-body > .container:first-child,
        .app-page-body > .container-fluid:first-child,
        .app-page-body > form:first-child {
            margin-top: 10px;
        }
        .page-header {
            margin-top: 5px !important;
            margin-bottom: 25px !important;
        }

        /* Modal & Backdrop Stacking Fix */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal-dialog {
            z-index: 1061 !important;
            position: relative;
        }
        @media (max-width: 991px) {
            .rt_nav_header.horizontal-layout {
                position: relative !important;
            }
            .app-page-body {
                padding-top: 35px !important;
            }
            .main-content {
                padding-top: 15px !important;
            }
        }
    </style>

    @vite(['resources/js/app.js'])
</head>

<body>
    <!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

    <!--=========================*
         Page Container
*===========================-->
    <div class="container-scroller">
        <!--=========================*
              Navigation
    *===========================-->
        <nav class="rt_nav_header horizontal-layout col-lg-12 col-12 p-0">
            <div class="top_nav flex-grow-1" style="background: #090e1a !important;">
                <div class="container-fluid px-3 px-md-4 d-flex flex-row h-100 align-items-center justify-content-between">
                    
                    <!-- Left: Dynamic Brand Logo + Company Name Pill -->
                    <div class="d-flex align-items-center">
                        @php
                            $dynCompName = \App\Models\Setting::get('company_name', 'TrendHub');
                            $init1 = strtoupper(substr($dynCompName, 0, 1));
                            $init2 = strlen($dynCompName) > 1 ? strtoupper(substr($dynCompName, 1, 1)) : '';
                        @endphp
                        <a class="nav_logo d-flex align-items-center text-decoration-none" href="{{ url('/home') }}">
                            <div class="header-pw-badge">
                                <span style="color: #ffffff;">{{ $init1 }}</span><span style="color: #a1a1aa;">{{ $init2 }}</span>
                            </div>
                            <div class="d-flex flex-column text-start justify-content-center">
                                <span style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; color: #ffffff; line-height: 1.15; letter-spacing: -0.2px;">{{ $dynCompName }}</span>
                                <span style="font-size: 10.5px; font-weight: 700; color: #a1a1aa; letter-spacing: 1px; line-height: 1;">Management</span>
                            </div>
                        </a>

                        <!-- Company / Branch Pill -->
                        <div class="header-company-pill d-none d-lg-flex">
                            <i class="far fa-user" style="color: #38bdf8; font-size: 13px;"></i>
                            <span style="color: #f1f5f9; font-weight: 600; font-size: 13px;">{{ $dynCompName }}</span>
                        </div>
                    </div>

                    <!-- Right: Phone + Support + Online + Sun + Notification + Profile -->
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        
                        <!-- Phone Number -->
                        <a href="tel:+923173836223" class="d-none d-md-flex align-items-center text-decoration-none" style="gap: 8px; color: #ffffff; font-size: 13.5px; font-weight: 600;">
                            <div style="width: 26px; height: 26px; border-radius: 50%; background: #2563eb; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; flex-shrink: 0;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <span class="text-white">+92 317 3836223</span>
                        </a>

                        <!-- Vertical Divider -->
                        <div class="d-none d-md-block" style="height: 20px; width: 1px; background: rgba(255,255,255,0.18);"></div>

                        <!-- Support Button -->
                        <a href="https://wa.me/923173836223" target="_blank" class="header-support-btn d-none d-md-flex" title="Contact ProWave Support">
                            <i class="fas fa-headset" style="color: #38bdf8; font-size: 13.5px;"></i>
                            <span>Support</span>
                        </a>

                        <!-- Online Status Pill -->
                        <div class="header-online-pill">
                            <span class="online-dot"></span>
                            <span class="d-none d-sm-inline">Online</span>
                        </div>

                        <!-- Sun / Theme Toggle -->
                        <div class="d-none d-sm-flex align-items-center justify-content-center text-white-50" style="width: 28px; height: 28px; cursor: pointer; font-size: 15px;">
                            <i class="fas fa-sun"></i>
                        </div>

                        <!-- Settings Link -->
                        @canany(['settings.view', 'settings.read'])
                            <a href="{{ route('settings.index') }}" class="d-none d-sm-flex align-items-center justify-content-center text-white-50 text-decoration-none" style="width: 28px; height: 28px; font-size: 15px;" title="Settings">
                                <i class="fas fa-cog" style="color: #94a3b8;"></i>
                            </a>
                        @endcanany

                        <!-- Notification Bell -->
                        <div class="dropdown position-relative" id="notificationLi">
                            <a class="d-flex align-items-center justify-content-center text-white-50 p-0 text-decoration-none"
                                id="notificationDropdown" href="#" data-toggle="dropdown" data-display="static"
                                aria-expanded="false" style="width: 32px; height: 32px; border-radius: 8px; cursor: pointer;">
                                <i class="fas fa-bell" style="font-size: 16px; color: #94a3b8;"></i>
                                <span class="badge badge-danger notification-badge"
                                    style="display: none; position: absolute; top: -2px; right: -2px; font-size: 9px; padding: 2px 4px; border-radius: 50%;">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list shadow-lg border-0"
                                aria-labelledby="notificationDropdown"
                                style="width: 320px; border-radius: 12px; margin-top: 10px; overflow: hidden;">
                                <div class="dropdown-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center"
                                    style="border-radius: 12px 12px 0 0;">
                                    <p class="mb-0 font-weight-bold text-dark">NOTIFICATIONS</p>
                                </div>
                                <div id="notificationList" style="max-height: 350px; overflow-y: auto;">
                                    <div class="text-center p-4">
                                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                    </div>
                                </div>
                                <div class="dropdown-footer text-center bg-light border-top p-2"
                                    style="position: sticky; bottom: 0; z-index: 10;">
                                    <a href="{{ route('notifications.index') }}"
                                        class="btn btn-primary btn-sm btn-block shadow-sm font-weight-bold">View
                                        All Notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="dropdown position-relative">
                            <a class="d-flex align-items-center justify-content-center p-0 text-decoration-none" 
                               href="#" data-toggle="dropdown" data-display="static" id="profileDropdown" style="cursor: pointer;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px; border: 1.5px solid rgba(255,255,255,0.45); color: #ffffff; font-size: 14px; transition: all 0.2s;">
                                    <i class="far fa-user"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg p-2"
                                aria-labelledby="profileDropdown" style="border-radius: 12px; margin-top: 10px; min-width: 220px;">
                                <div class="px-3 py-2 border-bottom mb-2 bg-light rounded">
                                    <p class="mb-0 text-dark font-weight-bold" style="font-size: 13px;">{{ Auth::user()->name }}</p>
                                    <small class="text-muted text-break" style="font-size: 11px;">{{ Auth::user()->email ?? '' }}</small>
                                </div>
                                @canany(['settings.view', 'settings.read'])
                                    <a class="dropdown-item d-flex align-items-center text-dark font-weight-bold py-2 mb-1" href="{{ route('settings.index') }}" style="border-radius: 8px;">
                                        <i class="fas fa-cog text-secondary mr-2"></i> Settings
                                    </a>
                                @endcanany
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center text-danger font-weight-bold py-2" style="border-radius: 8px; cursor: pointer;">
                                        <i class="fas fa-power-off text-danger mr-2"></i> Logout Account
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Mobile Hamburger Button -->
                        <button class="navbar-toggler align-self-center border-0 p-0 text-white ms-1 d-lg-none" type="button" data-toggle="minimize" style="font-size: 18px; outline: none; background: transparent; cursor: pointer;">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>

            </div>

            <!-- Notifications Polling Script -->
            <script>
                let _notifXhr1 = null;
                let _notifXhr2 = null;
                let _notifTimer = null;

                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        fetchNotifications();
                        _notifTimer = setInterval(fetchNotifications, 90000);
                    }, 5000);
                });

                function fetchNotifications() {
                    if (typeof $ === 'undefined') return;
                    if (_notifXhr1) { try { _notifXhr1.abort(); } catch(e){} _notifXhr1 = null; }
                    if (_notifXhr2) { try { _notifXhr2.abort(); } catch(e){} _notifXhr2 = null; }

                    _notifXhr1 = $.ajax({
                        url: "{{ route('notifications.fetch') }}",
                        method: 'GET',
                        timeout: 8000,
                        success: function(data) {
                            let notifications = data.notifications || [];
                            let count = data.count || 0;

                            _notifXhr2 = $.ajax({
                                url: "{{ route('customers.reminders') }}",
                                method: 'GET',
                                timeout: 8000,
                                success: function(reminderData) {
                                    let reminders = reminderData.reminders || [];
                                    let totalCount = count + reminders.length;
                                    if (totalCount > 0) {
                                        $('.notification-badge').text(totalCount).show();
                                    } else {
                                        $('.notification-badge').hide();
                                    }

                                    let html = '';
                                    reminders.forEach(r => {
                                        html += `
                                        <div class="dropdown-item p-3 notification-item reminder-item" style="white-space: normal; background-color: #fef2f2;">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3 mt-1" style="min-width: 36px;">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width:36px; height:36px; background-color: #fee2e2; color: #ef4444;">
                                                        <i class="fas fa-money-bill-wave" style="font-size:14px;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h6 class="font-weight-bold text-dark mb-1" style="font-size:14px; line-height:1.2;">Payment Due: ${r.name}</h6>
                                                        <button class="btn btn-xs btn-outline-danger snooze-btn" data-id="${r.id}" title="Snooze for today" style="padding: 2px 5px; font-size: 10px;">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-danger small mb-1" style="font-size:12px; line-height:1.4;">
                                                        Remaining Balance: <b>${r.balance}</b>
                                                    </p>
                                                    <p class="text-secondary small mb-0" style="font-size:10px; font-weight: 500;">
                                                        <i class="far fa-calendar-alt me-1"></i> Due Date: ${r.date}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>`;
                                    });

                                    if (notifications.length === 0 && reminders.length === 0) {
                                        html = `
                                            <div class="text-center p-5">
                                                <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 24px;"></i>
                                                <p class="text-muted small mb-0">No new notifications</p>
                                            </div>`;
                                    } else {
                                        notifications.forEach(n => {
                                            let iconBg = '#e3f2fd'; 
                                            let iconColor = '#2196f3'; 
                                            let iconClass = 'fa-info';
                                            if (n.type === 'sale_return') {
                                                iconBg = '#fff3e0'; 
                                                iconColor = '#ff9800'; 
                                                iconClass = 'fa-undo';
                                            }

                                            html += `
                                            <a class="dropdown-item p-3 notification-item" href="${n.action_url || '#'}" style="white-space: normal;">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-3 mt-1" style="min-width: 36px;">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width:36px; height:36px; background-color: ${iconBg}; color: ${iconColor};">
                                                            <i class="fas ${iconClass}" style="font-size:14px;"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="font-weight-bold text-dark mb-1" style="font-size:14px; line-height:1.2;">${n.title}</h6>
                                                        <p class="text-muted small mb-1" style="font-size:12px; line-height:1.4; color: #6c757d;">
                                                            ${n.message.substring(0, 60)}${n.message.length > 60 ? '...' : ''}
                                                        </p>
                                                        <p class="text-secondary small mb-0" style="font-size:10px; font-weight: 500;">
                                                            <i class="far fa-clock me-1"></i> ${new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>`;
                                        });
                                    }
                                    $('#notificationList').html(html);

                                    $('.snooze-btn').off('click').on('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        let id = $(this).data('id');
                                        let row = $(this).closest('.reminder-item');
                                        $.post("{{ url('customers/snooze-reminder') }}/" + id, { _token: "{{ csrf_token() }}" }, function(res) {
                                            if (res.success) {
                                                row.fadeOut(300, function() {
                                                    $(this).remove();
                                                    let currentCount = parseInt($('.notification-badge').text());
                                                    if (currentCount > 1) {
                                                        $('.notification-badge').text(currentCount - 1);
                                                    } else {
                                                        $('.notification-badge').hide();
                                                    }
                                                });
                                            }
                                        });
                                    });
                                }
                            });
                        }
                    });
                }
            </script>
            <div class="nav-bottom">
                <div class="container-fluid" style="padding: 0 20px;">
                    <ul class="nav page-navigation justify-content-center">
                        <!--=========================*
                              Home
                    *===========================-->
                        <li class="nav-item">
                            <a href="{{ url('/home') }}" class="nav-link"><i
                                    class="menu_icon fas fa-home"></i><span class="menu-title">Dashboard</span></a>

                        </li>
                        <!--=========================*
                              UI Features
                    *===========================-->
                        <li class="nav-item mega-menu">
                            @canany(['products.view', 'discount.products.view', 'categories.view', 'subcategories.view',
                                'brands.view', 'units.view', 'vendors.view', 'purchases.view', 'purchase_pos.create',
                                'warehouse.view', 'warehouse.stock.view', 'stock.transfer.view', 'stock.adjust.view', 'stock.adjust.create',
                                'sales.view', 'sales.create', 'customers.view', 'zones.view', 'sales.officers.view', 'receipts.voucher.view'])
                                <a href="#" class="nav-link">
                                     <i class="menu_icon fas fa-cogs"></i>
                                     <span class="menu-title">Management</span>
                                     <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <div class="col-group-wrapper row">
                                        <!-- Products & Categories -->
                                        @canany(['products.view', 'discount.products.view', 'categories.view',
                                            'subcategories.view', 'brands.view', 'units.view'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Products & Categories</p>
                                                <ul class="submenu-item">

                                                    @can('products.view')
                                                        <li><a href="{{ route('product') }}"><i class="fas fa-box"></i>
                                                                Products</a></li>
                                                    @endcan

                                                    @can('discount.products.view')
                                                        <li><a href="{{ route('discount.index') }}"><i class="fas fa-tags"></i>
                                                                Discount Products</a></li>
                                                    @endcan

                                                    @can('categories.view')
                                                        <li><a href="{{ route('Category.home') }}"><i class="fas fa-list"></i>
                                                                Category</a></li>
                                                    @endcan

                                                    @can('subcategories.view')
                                                        <li><a href="{{ route('subcategory.home') }}"><i
                                                                    class="fas fa-th-list"></i> Sub Category</a></li>
                                                    @endcan

                                                    @can('brands.view')
                                                        <li><a href="{{ route('Brand.home') }}"><i class="fas fa-trademark"></i>
                                                                Brands</a></li>
                                                    @endcan

                                                    @can('units.view')
                                                        <li><a href="{{ route('Unit.home') }}"><i
                                                                    class="fas fa-balance-scale"></i> Units</a></li>
                                                    @endcan

                                                </ul>
                                            </div>
                                        @endcanany
                                        <!-- Purchase & Inventory -->
                                        @canany(['vendors.view', 'purchases.view', 'purchase_pos.create'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Purchase & Inventory</p>
                                                <ul class="submenu-item">
                                                    @can('vendors.view')
                                                        <li><a href="{{ url('vendor') }}"><i class="fas fa-truck"></i> Vendor</a>
                                                        </li>
                                                    @endcan
                                                    @can('purchases.view')
                                                        <li><a href="{{ route('Purchase.home') }}"><i
                                                                    class="fas fa-shopping-cart"></i> Purchase</a>
                                                        </li>
                                                    @endcan
                                                    @can('purchase_pos.create')
                                                        <li><a href="{{ route('purchase-pos.index') }}"><i
                                                                    class="fas fa-cash-register"></i> Purchase POS</a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        @endcanany
                                        <!-- Accounts / Inventory -->
                                        @canany(['warehouse.view', 'warehouse.stock.view', 'stock.transfer.view', 'stock.adjust.view', 'stock.adjust.create'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Inventory Management</p>
                                                <ul class="submenu-item">
                                                    @can('warehouse.view')
                                                        <li><a href="{{ url('warehouse') }}"><i class="fas fa-warehouse"></i>
                                                                Warehouse</a></li>
                                                    @endcan
                                                    @can('warehouse.stock.view')
                                                        <li><a href="{{ url('warehouse_stocks') }}"><i class="fas fa-boxes"></i>
                                                                Warehouse Stock</a></li>
                                                    @endcan
                                                    @can('stock.transfer.view')
                                                        <li><a href="{{ url('stock_transfers') }}"><i
                                                                    class="fas fa-exchange-alt"></i> Stock Transfer</a></li>
                                                    @endcan
                                                    @canany(['stock.adjust.view', 'stock.adjust.create'])
                                                        <li><a href="{{ route('stock_adjustments.index') }}"><i
                                                                    class="fas fa-sliders-h"></i> Stock Adjustment</a></li>
                                                    @endcanany
                                                </ul>
                                            </div>
                                        @endcanany
                                        <!-- Customers & Sales -->
                                        @canany(['sales.view', 'sales.create', 'customers.view', 'sales.officers.view',
                                            'receipts.voucher.view', 'zones.view'])
                                            <div class="col-group col-md-3">
                                                <p class="category-heading">Sales & Customers</p>
                                                <ul class="submenu-item">
                                                    @can('sales.view')
                                                        <li><a href="{{ url('sale') }}"><i class="fas fa-receipt"></i>
                                                                Sales</a></li>
                                                    @endcan
                                                    @can('sales.create')
                                                        <li><a href="{{ route('pos.index') }}"><i class="fas fa-cash-register"></i>
                                                                POS System</a></li>
                                                    @endcan
                                                    @can('customers.view')
                                                        <li><a href="{{ url('customers') }}"><i class="fas fa-user"></i>
                                                                Customer</a></li>
                                                    @endcan
                                                    @can('customer_types.view')
                                                        <li><a href="{{ route('customer-types.index') }}"><i class="fas fa-tags"></i>
                                                                Customer Types</a></li>
                                                    @endcan
                                                    @can('zones.view')
                                                        <li><a href="{{ url('zone') }}"><i class="fas fa-map-marker-alt"></i>
                                                                Zone</a></li>
                                                    @endcan
                                                    @can('sales.officers.view')
                                                        <li><a href="{{ url('sales-officers') }}"><i class="fas fa-user-tie"></i>
                                                                Sales Officer</a></li>
                                                    @endcan
                                                    @can('receipts.voucher.view')
                                                        <li><a href="{{ route('all_recepit_vochers') }}"><i
                                                                    class="fas fa-file-invoice-dollar"></i>
                                                                Receipt Vouchers</a></li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        @endcanany
                                    </div>
                                </div>
                            @endcanany
                        </li>


                        <!-- Vouchers Menu -->
                        <li class="nav-item">
                            @canany(['chart.of.accounts.view', 'expense.voucher.view', 'receipts.voucher.view',
                                'journal.voucher.view', 'payment.voucher.view', 'income.voucher.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-clipboard-list"></i>
                                    <span class="menu-title">Vouchers</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('chart.of.accounts.view')
                                            <li><a href="{{ route('view_all') }}"><i class="fa-solid fa-money-bill-wave"></i>
                                                    Char Of Accounts</a></li>
                                        @endcan
                                        @can('expense.voucher.view')
                                            <li><a href="{{ route('all_expense_vochers') }}"><i
                                                        class="fa-solid fa-money-bill-wave"></i> Expense Voucher</a></li>
                                            <li><a href="{{ route('expense_categories.index') }}"><i
                                                        class="fa-solid fa-list-check"></i> Expense Categories</a></li>
                                        @endcan
                                        @can('receipts.voucher.view')
                                            <li><a href="{{ route('all_recepit_vochers') }}"><i
                                                        class="fa-solid fa-wallet"></i> Receipts Voucher</a></li>
                                        @endcan
                                        @can('journal.voucher.view')
                                            <li><a href="{{ route('vouchers.index', 'journal voucher') }}"><i
                                                        class="fa-solid fa-wallet"></i> Journal Voucher</a></li>
                                        @endcan
                                        @can('payment.voucher.view')
                                            <li><a href="{{ route('all_Payment_vochers') }}"><i
                                                        class="fa-solid fa-wallet"></i> Payment Voucher</a></li>
                                        @endcan
                                        @can('income.voucher.view')
                                            <li><a href="{{ route('vouchers.index', 'income voucher') }}"><i
                                                        class="fa-solid fa-wallet"></i> Income Voucher</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>
                        <li class="nav-item">
                            @canany(['item.stock.report.view', 'purchase.report.view', 'sale.report.view',
                                'customer.ledger.view', 'vendor.ledger.view', 'inventory.onhand.view', 'profit.loss.report.view',
                                'recovery.report.view', 'payable.report.view', 'parties.balance.report.view', 'aging.report.view', 'balance.sheet.report.view', 'executive.report.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-clipboard-list"></i>
                                    <span class="menu-title">Reports</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('executive.report.view')
                                            <li><a href="{{ route('report.executive') }}"><i class="fa-solid fa-crown text-warning"></i>
                                                    Executive Report</a></li>
                                        @endcan
                                        @can('item.stock.report.view')
                                            <li><a href="{{ route('report.item_stock') }}"><i class="fa-solid fa-users"></i>
                                                    Item Stock Report</a></li>
                                        @endcan
                                        @can('purchase.report.view')
                                            <li><a href="{{ route('report.purchase') }}"><i class="fa-solid fa-users"></i>
                                                    Purchase Report</a></li>
                                        @endcan
                                        @can('sale.report.view')
                                            <li><a href="{{ route('report.sale') }}"><i class="fa-solid fa-users"></i> Sale
                                                    Report</a></li>
                                            <li><a href="{{ route('report.product_sale_customer_wise') }}"><i class="fa-solid fa-users-between-lines"></i> Product Sale (Customer Wise)</a></li>
                                        @endcan
                                        @can('customer.ledger.view')
                                            <li><a href="{{ route('report.customer.ledger') }}"><i
                                                        class="fa-solid fa-book"></i> Customer Ledger</a></li>
                                        @endcan

                                        @can('vendor.ledger.view')
                                            <li><a href="{{ route('report.vendor.ledger') }}"><i
                                                        class="fa-solid fa-truck"></i> Vendor Ledger</a></li>
                                        @endcan


                                        @can('inventory.onhand.view')
                                            <li><a href="{{ route('reports.onhand') }}"><i class="fas fa-warehouse"></i>
                                                    Inventory On-Hand</a></li>
                                        @endcan
                                        
                                        @can('profit.loss.report.view')
                                            <li><a href="{{ route('report.profit_loss') }}"><i class="fa-solid fa-chart-line"></i>
                                                    Profit & Loss</a></li>
                                        @endcan
                                        
                                        @can('recovery.report.view')
                                            <li><a href="{{ route('report.recovery') }}"><i class="fa-solid fa-file-invoice-dollar"></i>
                                                    Recovery Report</a></li>
                                        @endcan

                                        @can('payable.report.view')
                                            <li><a href="{{ route('report.payable') }}"><i class="fa-solid fa-money-bill-wave"></i>
                                                    Payable Report</a></li>
                                        @endcan

                                        @can('parties.balance.report.view')
                                            <li><a href="{{ route('report.parties_balance') }}"><i class="fa-solid fa-users"></i>
                                                    Parties Balances</a></li>
                                        @endcan

                                        @can('aging.report.view')
                                            <li><a href="{{ route('report.aging') }}"><i class="fa-solid fa-hourglass-half"></i>
                                                    Aging Report</a></li>
                                        @endcan

                                        @can('balance.sheet.report.view')
                                            <li><a href="{{ route('report.balance_sheet') }}"><i class="fa-solid fa-scale-balanced"></i>
                                                    Balance Sheet</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>
                        
                        <!-- Cashbook / Checkbook -->
                        @canany(['checkbook.view', 'checkbook.create', 'checkbook.edit', 'checkbook.delete'])
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon fas fa-wallet"></i>
                                <span class="menu-title">Cashbook</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    <li><a href="{{ route('checkbook.index') }}"><i class="fas fa-lock"></i> Day Closings</a></li>
                                    <li><a href="{{ route('checkbook.transactions') }}"><i class="fas fa-history"></i> Cashbook History</a></li>
                                </ul>
                            </div>
                        </li>
                        @endcanany

                        <!-- HR Management Menu -->
                        <li class="nav-item">
                            @canany(['hr.departments.view', 'hr.designations.view', 'hr.employees.view',
                                'hr.attendance.view', 'hr.payroll.view', 'hr.leaves.view', 'hr.salary.structure.view',
                                'hr.shifts.view', 'hr.holidays.view', 'hr.loans.view', 'hr.biometric.devices.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-users-cog"></i>
                                    <span class="menu-title">HR Management</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('hr.departments.view')
                                            <li><a href="{{ route('hr.departments.index') }}"><i
                                                        class="fa-solid fa-building"></i> Departments</a></li>
                                        @endcan
                                        @can('hr.designations.view')
                                            <li><a href="{{ route('hr.designations.index') }}"><i
                                                        class="fa-solid fa-id-badge"></i> Designations</a></li>
                                        @endcan
                                        @can('hr.employees.view')
                                            <li><a href="{{ route('hr.employees.index') }}"><i
                                                        class="fa-solid fa-user-tie"></i> Employees</a></li>
                                        @endcan
                                        @can('hr.attendance.view')
                                            <li><a href="{{ route('hr.attendance.index') }}"><i
                                                        class="fa-solid fa-clock"></i> Attendance</a></li>
                                        @endcan
                                        @can('hr.payroll.view')
                                            <li><a href="{{ route('hr.payroll.index') }}"><i
                                                        class="fa-solid fa-money-check-alt"></i> Payroll</a></li>
                                        @endcan
                                        @can('hr.leaves.view')
                                            <li><a href="{{ route('hr.leaves.index') }}"><i
                                                        class="fa-solid fa-calendar-minus"></i> Leaves</a></li>
                                        @endcan
                                        @can('hr.salary.structure.view')
                                            <li><a href="{{ route('hr.salary-structure.index') }}"><i
                                                        class="fa-solid fa-coins"></i> Salary Structure</a></li>
                                        @endcan
                                        @can('hr.shifts.view')
                                            <li><a href="{{ route('hr.shifts.index') }}"><i class="fa-solid fa-clock"></i>
                                                    Shifts</a></li>
                                        @endcan
                                        @can('hr.holidays.view')
                                            <li><a href="{{ route('hr.holidays.index') }}"><i
                                                        class="fa-solid fa-calendar-alt"></i> Holidays</a></li>
                                        @endcan
                                        @can('hr.loans.view')
                                            <li><a href="{{ route('hr.loans.index') }}"><i
                                                        class="fa-solid fa-hand-holding-dollar"></i> Loans</a></li>
                                        @endcan
                                        @can('hr.biometric.devices.view')
                                            <li><a href="{{ route('hr.biometric-devices.index') }}"><i
                                                        class="fa-solid fa-fingerprint"></i> Biometric Devices</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>
                        <!-- User Management Menu -->
                        <li class="nav-item">
                            @canany(['users.view', 'roles.view', 'permissions.view', 'branches.view'])
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-clipboard-list"></i>
                                    <span class="menu-title">User Management</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('users.view')
                                            <li><a href="{{ route('users.index') }}"><i class="fa-solid fa-users"></i>
                                                    Users</a></li>
                                        @endcan
                                        @can('roles.view')
                                            <li><a href="{{ route('roles.index') }}"><i class="fa-solid fa-user-lock"></i>
                                                    Roles</a></li>
                                        @endcan
                                        @can('permissions.view')
                                            <li><a href="{{ route('permissions.index') }}"><i
                                                        class="fa-solid fa-user-lock"></i> Permissions</a></li>
                                        @endcan
                                        @can('branches.view')
                                            <li><a href="{{ route('branch.index') }}"><i class="fa-solid fa-code-branch"></i>
                                                    Branches</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </li>


                        <!-- Website Management -->
                        @canany(['website-settings.view', 'web_products.view', 'web_products.read', 'coupons.view', 'coupons.read', 'web_orders.view', 'web_orders.read', 'web_users.view', 'web_users.read'])
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="menu_icon fas fa-globe"></i>
                                    <span class="menu-title">Website Control</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-item">
                                        @can('website-settings.view')
                                            <li><a href="{{ route('website_settings.index') }}"><i class="fa-solid fa-cogs"></i> Website Settings</a></li>
                                        @endcan
                                        @canany(['web_products.view', 'web_products.read'])
                                            <li><a href="{{ route('web_products.index') }}"><i class="fa-solid fa-box"></i> Web Products</a></li>
                                        @endcan
                                        @canany(['coupons.view', 'coupons.read'])
                                            <li><a href="{{ route('admin.coupons.index') }}"><i class="fas fa-tags"></i> Coupons</a></li>
                                        @endcan
                                        @canany(['web_orders.view', 'web_orders.read'])
                                            <li><a href="{{ route('web_orders.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Web Dashboard</a></li>
                                            <li><a href="{{ route('web_orders.index') }}"><i class="fa-solid fa-shopping-cart"></i> Web Orders</a></li>
                                        @endcan
                                        @canany(['web_users.view', 'web_users.read'])
                                            <li><a href="{{ route('web_users.index') }}"><i class="fas fa-users"></i> Web Users</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endcanany

                        <!-- Mobile User Profile & Direct Logout -->
                        @if (auth()->check())
                            <li class="nav-item d-lg-none mt-3 pt-2 border-top" style="border-color: #e2e8f0 !important;">
                                <div class="px-3 py-2 bg-light rounded d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="d-block text-dark font-weight-bold" style="font-size: 13px;">{{ Auth::user()->name }}</span>
                                            <small class="text-muted" style="font-size: 11px;">{{ Auth::user()->email }}</small>
                                        </div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="px-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block font-weight-bold d-flex align-items-center justify-content-center py-2" style="border-radius: 8px; font-size: 13px; gap: 6px;">
                                        <i class="fas fa-power-off"></i> Logout Account
                                    </button>
                                </form>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>
        </nav>

        <div class="app-page-body">
            @yield('content')
        </div>

        <footer>
            <div class="footer-area">
                <p>&copy; Copyright 2025. All right reserved.  </p>
            </div>
        </footer>
    </div>
    <!-- Jquery Js -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <!-- bootstrap 4 js -->
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <!-- Owl Carousel Js -->
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <!-- Metis Menu Js -->
    <script src="{{ asset('assets/js/metisMenu.min.js') }}"></script>
    <!-- SlimScroll Js -->
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
    <!-- Slick Nav -->
    <script src="{{ asset('assets/js/jquery.slicknav.min.js') }}"></script>

    <!-- start amchart js -->
    <script src="{{ asset('assets/vendors/am-charts/js/ammap.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/worldLow.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/continentsLow.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/light.js') }}"></script>
    <!-- maps js -->
    <script src="{{ asset('assets/js/am-maps.js') }}"></script>

    <!-- Morris Chart -->
    <script src="{{ asset('assets/vendors/charts/morris-bundle/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/charts/morris-bundle/morris.js') }}"></script>

    <!-- Chart Js -->
    <script src="{{ asset('assets/vendors/charts/charts-bundle/Chart.bundle.js') }}"></script>

    <!-- C3 Chart -->
    <script src="{{ asset('assets/vendors/charts/c3charts/c3.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/charts/c3charts/d3-5.4.0.min.js') }}"></script>

    <!-- Data Table js -->
    <script src="{{ asset('assets/vendors/data-table/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/responsive.bootstrap.min.js') }}"></script>

    <!-- Sparkline Chart -->
    <script src="{{ asset('assets/vendors/charts/sparkline/jquery.sparkline.js') }}"></script>

    <!-- Home Script -->
    <script src="{{ asset('assets/js/home.js') }}"></script>

    <!-- Main Js -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

     {{-- Select2 JS - Local --}}
    <script src="{{ asset('assets/vendors/select2/js/select2.min.js') }}"></script>

    {{-- SweetAlert2 JS - Local (all.min.js includes CSS+JS bundled) --}}
    <script src="{{ asset('assets/vendors/sweetalert2/js/sweetalert2.all.min.js') }}"></script>

    <!-- Global Delete Function -->
    <script>
        function logoutAndDeleteFunction(button) {
            var url = button.getAttribute('data-url');
            var msg = button.getAttribute('data-msg') || "Are you sure you want to delete this?";
            Swal.fire({
                title: 'Are you sure?',
                text: msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>

    @yield('js')

    <!-- Global SweetAlert Toast/Popup -->
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}",
            });
        @endif
    </script>

    {{-- Anti-Ghost Mode: Disconnect BrowserSync to stop cross-tab navigation sync --}}
    <script>
        if (window.___browserSync___) {
            console.log('BrowserSync detected. Disconnecting socket to stop Ghost Mode sync.');
            window.___browserSync___.socket.disconnect();
        }
    </script>

    {{-- ✅ ANTI-FREEZE FIX: Remove stuck Bootstrap modal backdrops & overlays --}}
    <script>
        (function () {
            // Run cleanup on every page load
            function clearStuckOverlays() {
                // Remove stuck Bootstrap modal-backdrop divs
                document.querySelectorAll('.modal-backdrop').forEach(function (el) {
                    el.remove();
                });
                // Remove 'modal-open' class on body that prevents scrolling
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';

                // Remove any stuck open dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(function (el) {
                    el.classList.remove('show');
                });
                document.querySelectorAll('.show[data-toggle="dropdown"]').forEach(function (el) {
                    el.classList.remove('show');
                });
            }

            // Run on page load
            clearStuckOverlays();

            // Emergency: Press Escape key to clear all stuck overlays/modals
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    clearStuckOverlays();
                    // Also close any open Bootstrap modals
                    document.querySelectorAll('.modal.show').forEach(function (modal) {
                        $(modal).modal('hide');
                    });
                }
            });

            // Auto-detect freeze: if a click occurs on body/backdrop with no active modal, clear stuck overlays
            document.body.addEventListener('click', function (e) {
                var openModals = document.querySelectorAll('.modal.show');
                if (openModals.length === 0) {
                    if (e.target === document.body || e.target.classList.contains('modal-backdrop')) {
                        clearStuckOverlays();
                    }
                }
            });

            // Run cleanup every 30 seconds as a safety net
            setInterval(function () {
                // Only clean if a backdrop exists but no modal is visible
                var backdrops = document.querySelectorAll('.modal-backdrop');
                var openModals = document.querySelectorAll('.modal.show');
                if (backdrops.length > 0 && openModals.length === 0) {
                    clearStuckOverlays();
                }
            }, 30000);

            // Fix for Navigation links with href="#" causing page jumps and potential layout freezes
            document.querySelectorAll('.nav-link[href="#"]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent URL hash and page jump
                });
            });

            // Prevent clicks inside the submenu from bubbling up and instantly closing the menu (Touch/Mobile fix)
            document.querySelectorAll('.submenu').forEach(function(submenu) {
                submenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
            
            // Failsafe: Remove any rogue overlay class on body immediately
            document.body.classList.remove('modal-open', 'sidebar_collapsed');
        })();
    </script>
    
    {{-- CSS Failsafe to ensure no invisible layer blocks the screen --}}
    <style>
        body.modal-open { padding-right: 0 !important; overflow: auto !important; }
        .modal-backdrop.fade:not(.show) { display: none !important; pointer-events: none; }
        /* FIX: Do NOT disable pointer-events on swal2-container — it blocks button clicks */
        /* Only hide container when truly not visible (swal2-hide means it's animating out) */
        .swal2-container.swal2-hide { pointer-events: none !important; }
        /* Ensure SweetAlert2 is always on top */
        .swal2-container { z-index: 99999 !important; }
    </style>

    {{-- Flatpickr JS --}}
    
    <script src="/assets/js/flatpickr.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.datepicker-custom', {
                dateFormat: "Y-m-d", // The value submitted to the server
                altInput: true,      // Show a secondary visually formatted input
                altFormat: "d/m/Y",  // Day/Month/Year
                allowInput: true     // Allow typing
            });
        });
    </script>
    @stack('scripts')
</body>

</html>

<!DOCTYPE html>
<html style="--primary-rgb: <?= env("colors.primary.rgb", "32, 143, 132"); ?>;" lang="en" dir="ltr" data-nav-layout="vertical"
    data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="open" loader="enable">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> Dashboard - <?= env("app.title", "Unizflow"); ?> </title>

    <!-- Favicon -->
    <link rel="icon" href="<?= env("favicon", "/assets/royalpay-favicon.png"); ?>" type="image/x-icon">

    <!-- Choices JS -->
    <script src="/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- Main Theme Js -->
    <script src="/assets/js/main.js"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style Css -->
    <link href="/assets/css/styles.min.css" rel="stylesheet">

    <!-- Icons Css -->
    <link href="/assets/css/icons.css" rel="stylesheet">

    <!-- Node Waves Css -->
    <link href="/assets/libs/node-waves/waves.min.css" rel="stylesheet">

    <!-- Simplebar Css -->
    <link href="/assets/libs/simplebar/simplebar.min.css" rel="stylesheet">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">

    <!-- Choices Css -->
    <link rel="stylesheet" href="/assets/libs/choices.js/public/assets/styles/choices.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <link rel="stylesheet" href="/assets/libs/jsvectormap/css/jsvectormap.min.css">

    <link rel="stylesheet" href="/assets/libs/swiper/swiper-bundle.min.css">


    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.0/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="/assets/libs/sweetalert2/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script>window.onloads = []; window.intervals = []; </script>

</head>

<body>

    <style>
        /* rgb(32, 143, 132) */
        .app-sidebar .side-menu__item.active {
            background: linear-gradient(124deg, rgba(<?= env("colors.primary.rgb", "32, 143, 132"); ?>, 0.7) 0%, rgb(60,90,118) 100%);
            box-shadow: 0px 2px 6px 0px rgba(115, 103, 240, .3);
            color: #fff !important;
        }

        .app-sidebar .side-menu__item.active .side-menu__label {
            color: white;
        }

        .app-sidebar .side-menu__item.active .side-menu__icon {
            color: #ffffff;
        }

        .header .header-content {
            display: flex;
            height: min-content;
            min-height: 55px;
            align-items: center;
        }

        .app-sidebar .slide-menu.child1 .side-menu__item:before {
            display: none !important;
        }

        .app-sidebar .slide-menu.child1 .side-menu__item,
        .app-sidebar .slide-menu.child2 .side-menu__item {
            padding: .45rem 0.6rem;
        }

        .slide.has-sub>.side-menu__item
        {
          margin-block: 0px !important;
        }

        #sidebar-scroll{
            overflow-y: scroll !important;
        }

        div.d-md-flex.justify-content-between.align-items-center.col-12.dt-layout-full.col-md {
         padding: 0px !important;
        }

        .select2-selection__rendered{
            min-width: 130px;
        }

        .breadcrumb-item{
            cursor: pointer;
        }
    </style>

    <!--Loader -->
    <!-- <div id="loader" >
        <img src="/assets/images/media/loader.svg" alt="">
    </div> -->
    <!-- Loader -->

    <div class="page">
        <!-- app-header -->
        <header class="app-header">

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid shadow-sm">

                <!-- Start::header-content-left -->
                <div class="header-content-left">

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="/" class="header-logo">
                                <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" class="desktop-logo">
                                <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" class="toggle-logo">
                                <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" class="desktop-dark">
                                <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" class="toggle-dark">
                                <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" class="desktop-white">
                                <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" class="toggle-white">
                            </a>
                        </div>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link -->
                        <a aria-label="Hide Sidebar"
                            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                            data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->


                    <!-- <div class="header-element align-items-center">
                      
                       <input type="text" class="form-control" id="search" placeholder="Search..." onkeyup="search(this.value)">
                       
                    </div> -->

                </div>
                <!-- End::header-content-left -->

                <!-- Start::header-content-right -->
                <div class="header-content-right">

                 

                    <!-- Start::header-element -->
                    <div class="header-element notifications-dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                            <i class="bx bx-bell header-link-icon"></i>
                            <span class="badge bg-secondary rounded-pill header-icon-badge pulse pulse-secondary"
                                id="notification-icon-badge">0</span>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                            <div class="p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="mb-0 fs-17 fw-semibold">Notifications</p>
                                    <span class="badge bg-secondary-transparent" id="notifiation-data">0 Unread</span>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <ul class="list-unstyled mb-0" id="header-notification-scroll">

                            </ul>
                            <!--<div class="p-3 empty-header-item1 border-top">-->
                            <!--    <div class="d-grid">-->
                            <!--        <a href="notifications.html" class="btn btn-primary">View All</a>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="p-5 empty-item1">
                                <div class="text-center">
                                    <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                                        <i class="ri-notification-off-line fs-2"></i>
                                    </span>
                                    <h6 class="fw-semibold mt-3">No New Notifications</h6>
                                </div>
                            </div>
                        </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->


                    <!-- Start::header-element -->
                    <div class="header-element header-fullscreen">
                        <!-- Start::header-link -->
                        <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                            <i class="bx bx-fullscreen full-screen-open header-link-icon"></i>
                            <i class="bx bx-exit-fullscreen full-screen-close header-link-icon d-none"></i>
                        </a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="me-sm-2 me-0">
                                <span class="avatar avatar-sm me-2 bg-primary"><?php 
                                  //initials of name
                                    $name = $admin['name'];
                                    $name = explode(" ", $name);
                                    $initials = "";
                                    foreach ($name as $n) {
                                        $initials .= strtoupper($n[0]);
                                    }
                                    echo $initials;
                                ?></span>
                                </div>
                                <div class="d-sm-block d-none">
                                    <p class="fw-semibold mb-0 lh-1"><?= $admin['name']; ?></p>
                                    <span class="op-7 fw-normal d-block fs-11"><i class="ti ti-id"></i>&nbsp;Administrator</span>
                                </div>
                            </div>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                            aria-labelledby="mainHeaderProfile">
                            <li><a class="dropdown-item d-flex" style="cursor: pointer;"
                                    onclick="navigate('/home/change_password'); closeDropdown(this);"><i
                                        class="ti ti-lock fs-18 me-2 op-7"></i>Change Password</a></li>

                            <li><a class="dropdown-item d-flex" href="/admin/logout"><i
                                        class="ti ti-logout fs-18 me-2 op-7"></i>Log Out</a></li>
                        </ul>
                    </div>
                    <!-- End::header-element -->



                </div>
                <!-- End::header-content-right -->

            </div>
            <!-- End::main-header-container -->

        </header>
        <!-- /app-header -->
        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <div class="d-flex gap-2 justify-content-center align-items-center">
                    <a href="/" class="header-logo">
                        <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" style="height: 50px;" class="desktop-logo">
                        <img src="<?= env("favicon", "/assets/royalpay-favicon.png"); ?>" alt="logo" style="height: 50px;" class="toggle-logo">
                        <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" style="height: 50px;" class="desktop-dark">
                        <img src="<?= env("favicon", "/assets/royalpay-favicon.png"); ?>" alt="logo" style="height: 50px;" class="toggle-dark">
                        <img src="<?= env("logo", "/assets/royalpay-logo.png"); ?>" alt="logo" style="height: 50px;" class="desktop-white">
                        <img src="<?= env("favicon", "/assets/royalpay-favicon.png"); ?>" alt="logo" style="height: 50px;" class="toggle-white">
                    </a>
                   
                </div>

            </div>
            <!-- End::main-sidebar-header -->


            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                            viewBox="0 0 24 24">
                            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                        </svg>
                    </div>
                    <ul class="main-menu">
                        <!-- Start::slide__category -->
                        <!--<li class="slide__category"><span class="category-name"></span></li>-->
                        <!-- End::slide__category -->
 
                      
                        <!-- Start::slide -->
                        <li class="slide">
                            <a onclick="navigate('/admin/dashboard'); activeNavigation(this);"
                                class="side-menu__item <?php if ($page == "dashboard") {
                                    echo "active";
                                } ?>">
                                <i class="ti ti-home side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>
                        <!-- End::slide -->
                     
                        
                        
                        
                         <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["active-users","inactive-users","locked-users"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-users side-menu__icon"></i>
                                <span class="side-menu__label">Users</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                               
                                <li class="slide"><a onclick="navigate('/admin/users/active'); activeNavigation(this);" class="side-menu__item <?=  $page == "active-users"?"active":""?>"><i class="ti ti-circle-check mx-2"></i> Active Users</a></li>
                               
                                <li class="slide"><a onclick="navigate('/admin/users/inactive'); activeNavigation(this);" class="side-menu__item <?=  $page == "inactive-users"?"active":""?>"><i class="ti ti-circle-minus mx-2"></i>Inactive Users</a></li>
                                
                                <li class="slide"><a onclick="navigate('/admin/users/locked'); activeNavigation(this);" class="side-menu__item <?=  $page == "locked-users"?"active":""?>"><i class="ti ti-lock mx-2"></i>Locked Users</a></li>
                                
                            </ul>
                        </li>
                        <!-- End::slide -->
                      
                        

                      
                        <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["add_package","packages"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-box side-menu__icon"></i>
                                <span class="side-menu__label">Packages</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                            
                                <li class="slide"><a onclick="navigate('/admin/packages/add'); activeNavigation(this);" class="side-menu__item <?=  $page == "add_package"?"active":""?>"><i class="ti ti-circle-plus mx-2"></i> New Package</a></li>
                               
                                <li class="slide"><a onclick="navigate('/admin/packages'); activeNavigation(this);" class="side-menu__item <?=  $page == "packages"?"active":""?>"><i class="ti ti-list mx-2"></i>Package List</a></li>
                            
                               
                            </ul>
                        </li>
                        <!-- End::slide -->
                       
                         <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["payin_providers","payout_providers","qr_providers","provider_slabs"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-building-bank side-menu__icon"></i>
                                <span class="side-menu__label">Providers</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                                
                                <li class="slide"><a onclick="navigate('/admin/providers/payin'); activeNavigation(this);" class="side-menu__item <?=  $page == "payin_providers"?"active":""?>"><i class="ti ti-arrow-down-left mx-2"></i> Payin Providers</a></li>
                
                                <li class="slide"><a onclick="navigate('/admin/providers/payout'); activeNavigation(this);" class="side-menu__item <?=  $page == "payout_providers"?"active":""?>"><i class="ti ti-arrow-up-right mx-2"></i>Payout Providers</a></li>
                                
                                <li class="slide"><a onclick="navigate('/admin/provider-slabs'); activeNavigation(this);" class="side-menu__item <?=  $page == "provider_slabs"?"active":""?>"><i class="ti ti-list-check mx-2"></i>Provider Slabs</a></li>
                                
                            </ul>
                        </li>
                        <!-- End::slide -->
                      
                          <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["pending_fund_requests","completed_fund_requests","rejected_fund_requests","banks"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-cash side-menu__icon"></i>
                                <span class="side-menu__label">Fund Requests</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                               
                               
                                 <li class="slide"><a onclick="navigate('/admin/fund-requests/pending'); activeNavigation(this);" class="side-menu__item <?=  $page == "pending_fund_requests"?"active":""?>"><i class="ti ti-history mx-2"></i> Pending</a></li>
                
                                <li class="slide"><a onclick="navigate('/admin/fund-requests/completed'); activeNavigation(this);" class="side-menu__item <?=  $page == "completed_fund_requests"?"active":""?>"><i class="ti ti-circle-check mx-2"></i> Completed</a></li>
                                
                                <li class="slide"><a onclick="navigate('/admin/fund-requests/rejected'); activeNavigation(this);" class="side-menu__item <?=  $page == "rejected_fund_requests"?"active":""?>"><i class="ti ti-alert-circle mx-2"></i> Rejected</a></li>
                
                                
                                <li class="slide"><a onclick="navigate('/admin/fund-request-banks'); activeNavigation(this);" class="side-menu__item <?=  $page == "banks"?"active":""?>"><i class="ti ti-building-bank mx-2"></i>Banks</a></li>
                                 
                               
                            </ul>
                        </li>
                        <!-- End::slide -->
                        
                          <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["add_credits","pending_credits","completed_credits"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-credit-card side-menu__icon"></i>
                                <span class="side-menu__label">User Credits</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                               
                             
                                <li class="slide"><a onclick="navigate('/admin/credits/add'); activeNavigation(this);" class="side-menu__item <?=  $page == "add_credits"?"active":""?>"><i class="ti ti-circle-plus mx-2"></i> Add Credit</a></li>
                            
                                <li class="slide"><a onclick="navigate('/admin/credits/pending'); activeNavigation(this);" class="side-menu__item <?=  $page == "pending_credits"?"active":""?>"><i class="ti ti-history mx-2"></i> Pending</a></li>
                
                                <li class="slide"><a onclick="navigate('/admin/credits/completed'); activeNavigation(this);" class="side-menu__item <?=  $page == "completed_credits"?"active":""?>"><i class="ti ti-circle-check mx-2"></i> Completed</a></li>
                                
                              
                               
                            </ul>
                        </li>
                        <!-- End::slide -->
                       
                         <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["all_transactions","payin_transactions","payout_transactions","qr_transactions"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-arrows-right-left side-menu__icon"></i>
                                <span class="side-menu__label">Transactions</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                               
                                <li class="slide"><a onclick="navigate('/admin/transactions/all'); activeNavigation(this);" class="side-menu__item <?=  $page == "all_transactions"?"active":""?>"><i class="ti ti-list mx-2"></i> All Transactions</a></li>
                
                                <li class="slide"><a onclick="navigate('/admin/transactions/payin'); activeNavigation(this);" class="side-menu__item <?=  $page == "payin_transactions"?"active":""?>"><i class="ti ti-arrow-down-left mx-2"></i> Payin Transactions</a></li>
                                
                                <li class="slide"><a onclick="navigate('/admin/transactions/payout'); activeNavigation(this);" class="side-menu__item <?=  $page == "payout_transactions"?"active":""?>"><i class="ti ti-arrow-up-right mx-2"></i> Payout Transactions</a></li>
                
                               
                               
                               
                            </ul>
                        </li>
                        <!-- End::slide -->
                      
                         <!-- Start::slide -->
                        <li class="slide has-sub <?= in_array($page,["hold_transactions","released_transactions"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-history side-menu__icon"></i>
                                <span class="side-menu__label">Hold Transactions</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                               
                                <li class="slide"><a onclick="navigate('/admin/holdtransactions/hold'); activeNavigation(this);" class="side-menu__item <?=  $page == "hold_transactions"?"active":""?>"><i class="ti ti-circle-minus mx-2"></i> On Hold</a></li>
                
                                <li class="slide"><a onclick="navigate('/admin/holdtransactions/released'); activeNavigation(this);" class="side-menu__item <?=  $page == "released_transactions"?"active":""?>"><i class="ti ti-circle-check mx-2"></i> Released</a></li>
                            </ul>
                        </li>
                        <!-- End::slide -->
                      
                         <!-- Start::slide -->
                        <li class="slide">
                            <a onclick="navigate('/admin/wallet'); activeNavigation(this);"
                                class="side-menu__item <?php if ($page == "wallet") {
                                    echo "active";
                                } ?>">
                                <i class="ti ti-wallet side-menu__icon"></i>
                                <span class="side-menu__label">Wallet Ledger</span>
                            </a>
                        </li>
                         
                        <li class="slide has-sub <?= in_array($page,["settlement_pending","settlement_completed","settlement_hold","settlement_packages","add_settlement_package"])?"open":"" ?>">
                            <a class="side-menu__item">
                                <i class="ti ti-receipt side-menu__icon"></i>
                                <span class="side-menu__label">Settlements</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1" data-popper-placement="bottom" style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate3d(119.5px, 173.5px, 0px);">
                               
                                <li class="slide"><a onclick="navigate('/admin/settlements/pending'); activeNavigation(this);" class="side-menu__item <?=  $page == "settlement_pending"?"active":""?>"><i class="ti ti-history mx-2"></i> Pending</a></li>
                                
                                <li class="slide"><a onclick="navigate('/admin/settlements/hold'); activeNavigation(this);" class="side-menu__item <?=  $page == "settlement_hold"?"active":""?>"><i class="ti ti-forbid-2 mx-2"></i> Hold</a></li>
                
                                <li class="slide"><a onclick="navigate('/admin/settlements/completed'); activeNavigation(this);" class="side-menu__item <?=  $page == "settlement_completed"?"active":""?>"><i class="ti ti-circle-check mx-2"></i> Completed</a></li>
                          
                                <li class="slide"><a onclick="navigate('/admin/settlement-packages'); activeNavigation(this);" class="side-menu__item <?=  $page == "settlement_packages"?"active":""?>"><i class="ti ti-box mx-2"></i> Settlement Packages</a></li>

                                <li class="slide"><a onclick="navigate('/admin/settlement-packages/add'); activeNavigation(this);" class="side-menu__item <?=  $page == "add_settlement_package"?"active":""?>"><i class="ti ti-circle-plus mx-2"></i> Add Package</a></li>

                            </ul>
                        </li>
                        <!-- End::slide -->
                       
                       
                      
                     
                        
                        
                        
                       
                     
                         <!-- Start::slide -->
                        <li class="slide">
                            <a onclick="navigate('/admin/profit'); activeNavigation(this);"
                                class="side-menu__item <?php if ($page == "profit") {
                                    echo "active";
                                } ?>">
                                <i class="ti ti-chart-arrows-vertical side-menu__icon"></i>
                                <span class="side-menu__label">Admin Profit</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide">
                            <a onclick="navigate('/admin/error-logs'); activeNavigation(this);"
                                class="side-menu__item <?php if ($page == "error_logs") {
                                    echo "active";
                                } ?>">
                                <i class="ti ti-bug side-menu__icon"></i>
                                <span class="side-menu__label">Error Logs</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                         <!-- Start::slide -->
                        <li class="slide">
                            <a onclick="navigate('/admin/settings'); activeNavigation(this);"
                                class="side-menu__item <?php if ($page == "settings") {
                                    echo "active";
                                } ?>">
                                <i class="ti ti-settings side-menu__icon"></i>
                                <span class="side-menu__label">Settings</span>
                            </a>
                        </li>
                        <!-- End::slide -->
                      
                        
                      
                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                            width="24" height="24" viewBox="0 0 24 24">
                            <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                        </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->

        <style>
            .app-sidebar .side-menu__item:hover {
                background-color: transparent !important;
                cursor: pointer;
            }

            .app-sidebar .side-menu__item.active:hover {
                background-color: #f3f6f8 !important;
                cursor: pointer;
            }

            #loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: var(--custom-white);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
        </style>



        <!-- Start::app-content -->
        <div class="main-content app-content position-relative">
    <div id="dashLoader" class="d-none justify-content-center align-items-center" style=" position: absolute; top:0;left:0; height: 100%; width: 100%; z-index:9999;">
        <div class="btn bg-white shadow-lg" style="margin-top: -10% !important;">
           <p  class="myloader mb-0"></p>
        </div>
    </div>
            <div class="container-fluid" id="innerBody">
                
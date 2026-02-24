<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }} | Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>

    <style>
        .icon-link {
            color: white;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .icon-link.active {
            color: #0A6FB8;
        }

        .icon-link svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
    </style>

    @yield('local_css')
</head>

<body>

    <section class="admin-parent">

        {{-- sidebar section --}}
        <aside class="sidebar-section">
            <div class="logo-divide">
                <img src="{{ asset('assets/img/logo-white.svg') }}" class="large-logo" alt="large size">
                <img src="{{ asset('assets/img/favicon.png') }}" class="small-logo" alt="small size">
            </div>

            <div class="main-menu">
                <h6 class="sidebar-header">main menu</h6>

                {{-- Overview --}}
                {{-- <a href="{{ url('/home') }}" class="icon-link {{ Request::is('home') ? 'active' : '' }}">
                    <svg viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.38925 2.13012L2.57133 5.28012C1.93383 5.80512 1.41675 6.92262 1.41675 7.77012V13.3276C1.41675 15.0676 2.7555 16.4926 4.39883 16.4926H12.6013C14.2447 16.4926 15.5834 15.0676 15.5834 13.3351V7.87512C15.5834 6.96762 15.0097 5.80512 14.3084 5.28762L9.93092 2.04012C8.93925 1.30512 7.3455 1.34262 6.38925 2.13012Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.5 13.4924V11.2424" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h6>overview</h6>
                </a> --}}

                {{-- Equipment --}}
                <a href="{{ route('equipments.index') }}" class="icon-link {{ Request::is('equipment') ? 'active' : '' }}">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.75 8.125H17.7586L15 5.36641C14.8843 5.24983 14.7467 5.1574 14.595 5.0945C14.4433 5.0316 14.2806 4.99948 14.1164 5H10.9375V3.125H12.8125C12.9783 3.125 13.1372 3.05915 13.2544 2.94194C13.3717 2.82473 13.4375 2.66576 13.4375 2.5C13.4375 2.33424 13.3717 2.17527 13.2544 2.05806C13.1372 1.94085 12.9783 1.875 12.8125 1.875H7.8125C7.64674 1.875 7.48777 1.94085 7.37056 2.05806C7.25335 2.17527 7.1875 2.33424 7.1875 2.5C7.1875 2.66576 7.25335 2.82473 7.37056 2.94194C7.48777 3.05915 7.64674 3.125 7.8125 3.125H9.6875V5H5C4.66848 5 4.35054 5.1317 4.11612 5.36612C3.8817 5.60054 3.75 5.91848 3.75 6.25V10.3125H1.875V8.4375C1.875 8.27174 1.80915 8.11277 1.69194 7.99556C1.57473 7.87835 1.41576 7.8125 1.25 7.8125C1.08424 7.8125 0.925268 7.87835 0.808058 7.99556C0.690848 8.11277 0.625 8.27174 0.625 8.4375V13.4375C0.625 13.6033 0.690848 13.7622 0.808058 13.8794C0.925268 13.9967 1.08424 14.0625 1.25 14.0625C1.41576 14.0625 1.57473 13.9967 1.69194 13.8794C1.80915 13.7622 1.875 13.6033 1.875 13.4375V11.5625H3.75V13.1789C3.74948 13.3431 3.7816 13.5058 3.8445 13.6575C3.9074 13.8092 3.99983 13.9468 4.11641 14.0625L7.1875 17.1336C7.30315 17.2502 7.44082 17.3426 7.59251 17.4055C7.7442 17.4684 7.90688 17.5005 8.07109 17.5H14.1164C14.2806 17.5005 14.4433 17.4684 14.595 17.4055C14.7467 17.3426 14.8843 17.2502 15 17.1336L17.7586 14.375H18.75C19.0815 14.375 19.3995 14.2433 19.6339 14.0089C19.8683 13.7745 20 13.4565 20 13.125V9.375C20 9.04348 19.8683 8.72554 19.6339 8.49112C19.3995 8.2567 19.0815 8.125 18.75 8.125ZM18.75 13.125H17.5C17.4179 13.1249 17.3366 13.141 17.2607 13.1724C17.1848 13.2038 17.1159 13.2498 17.0578 13.3078L14.1164 16.25H8.07109L5 13.1789V6.25H14.1164L17.0578 9.19219C17.1159 9.25021 17.1848 9.29622 17.2607 9.32759C17.3366 9.35895 17.4179 9.37506 17.5 9.375H18.75V13.125Z" fill="currentColor"/>
                    </svg>
                    <h6>equipment</h6>
                </a>

                {{-- Maintenance --}}
                <a href="{{ url('maintenance') }}" class="icon-link {{ Request::is('maintenance') ? 'active' : '' }}">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.6842 2.08325C13.5026 2.08325 14.2772 2.26992 14.9672 2.60242L13.6322 3.92742C13.4492 4.10837 13.304 4.32381 13.2049 4.56125C13.1058 4.7987 13.0548 5.05345 13.0548 5.31075C13.0548 5.56805 13.1058 5.8228 13.2049 6.06025C13.304 6.2977 13.4492 6.51313 13.6322 6.69409C14.003 7.06108 14.5036 7.26693 15.0253 7.26693C15.547 7.26693 16.0476 7.06108 16.4184 6.69409L17.6072 5.51409C17.8072 6.06492 17.9167 6.65909 17.9167 7.27825C17.9167 10.1478 15.5738 12.4737 12.6842 12.4737C11.9926 12.4737 11.3326 12.3403 10.7288 12.0987L5.44591 17.3437C5.07531 17.7107 4.5748 17.9166 4.0532 17.9166C3.5316 17.9166 3.03109 17.7107 2.66049 17.3437C2.47776 17.1627 2.3327 16.9474 2.23371 16.71C2.13471 16.4727 2.08374 16.2181 2.08374 15.961C2.08374 15.7038 2.13471 15.4492 2.23371 15.2119C2.3327 14.9745 2.47776 14.7592 2.66049 14.5783L7.89674 9.37909C7.60217 8.71793 7.45039 8.00206 7.45132 7.27825C7.45132 4.4095 9.79424 2.08325 12.6842 2.08325Z" stroke="currentColor" stroke-linejoin="round"/>
                    </svg>
                    <h6>maintenance</h6>
                </a>

                {{-- Users --}}
                <a href="{{ url('users') }}" class="icon-link {{ Request::is('users') ? 'active' : '' }}">
                    <svg viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.8332 5.96675C15.7805 5.95842 15.7189 5.95842 15.6661 5.96675C14.4522 5.92508 13.4846 4.98342 13.4846 3.81675C13.4846 2.62508 14.4962 1.66675 15.7541 1.66675C17.0119 1.66675 18.0235 2.63342 18.0235 3.81675C18.0147 4.98342 17.0471 5.92508 15.8332 5.96675Z" stroke="currentColor" stroke-width="1.1875" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.9273 12.0333C16.1324 12.225 17.4606 12.025 18.393 11.4333C19.6333 10.65 19.6333 9.36664 18.393 8.58331C17.4518 7.99164 16.106 7.79163 14.9009 7.99163" stroke="currentColor" stroke-width="1.1875" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.25131 5.96675C5.30409 5.95842 5.36566 5.95842 5.41844 5.96675C6.63233 5.92508 7.59992 4.98342 7.59992 3.81675C7.59992 2.62508 6.58835 1.66675 5.33048 1.66675C4.07261 1.66675 3.06104 2.63342 3.06104 3.81675C3.06983 4.98342 4.03743 5.92508 5.25131 5.96675Z" stroke="currentColor" stroke-width="1.1875" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.15742 12.0333C4.95233 12.225 3.62409 12.025 2.69168 11.4333C1.45141 10.65 1.45141 9.36664 2.69168 8.58331C3.63289 7.99164 4.97872 7.79163 6.18381 7.99163" stroke="currentColor" stroke-width="1.1875" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.5556 12.1916C10.5029 12.1833 10.4413 12.1833 10.3885 12.1916C9.17462 12.1499 8.20703 11.2083 8.20703 10.0416C8.20703 8.84994 9.2186 7.8916 10.4765 7.8916C11.7343 7.8916 12.7459 8.85827 12.7459 10.0416C12.7371 11.2083 11.7695 12.1583 10.5556 12.1916Z" stroke="currentColor" stroke-width="1.1875" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.99588 14.8166C6.7556 15.5999 6.7556 16.8832 7.99588 17.6666C9.40329 18.5582 11.7079 18.5582 13.1153 17.6666C14.3556 16.8832 14.3556 15.5999 13.1153 14.8166C11.7167 13.9332 9.40329 13.9332 7.99588 14.8166Z" stroke="currentColor" stroke-width="1.1875" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h6>Users</h6>
                </a>

                {{-- Reports --}}
                <a href="{{ url('reports') }}" class="icon-link {{ Request::is('reports') ? 'active' : '' }}">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33325 7.5V16.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.66675 3.33325V16.6666" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 9.16675V16.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.3333 5.83325V16.6666" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.6667 11.6667V16.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h6>reports</h6>
                </a>

                {{-- Settings --}}
                <a href="{{ url('settings') }}" class="icon-link {{ Request::is('settings') ? 'active' : '' }}">
                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 11.25C10.2426 11.25 11.25 10.2426 11.25 9C11.25 7.75736 10.2426 6.75 9 6.75C7.75736 6.75 6.75 7.75736 6.75 9C6.75 10.2426 7.75736 11.25 9 11.25Z" stroke="currentColor" stroke-width="1.1875" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.5 9.66007V8.34007C1.5 7.56007 2.1375 6.91507 2.925 6.91507C4.2825 6.91507 4.8375 5.95507 4.155 4.77757C3.765 4.10257 3.9975 3.22507 4.68 2.83507L5.9775 2.09257C6.57 1.74007 7.335 1.95007 7.6875 2.54257L7.77 2.68507C8.445 3.86257 9.555 3.86257 10.2375 2.68507L10.32 2.54257C10.6725 1.95007 11.4375 1.74007 12.03 2.09257L13.3275 2.83507C14.01 3.22507 14.2425 4.10257 13.8525 4.77757C13.17 5.95507 13.725 6.91507 15.0825 6.91507C15.8625 6.91507 16.5075 7.55257 16.5075 8.34007V9.66007C16.5075 10.4401 15.87 11.0851 15.0825 11.0851C13.725 11.0851 13.17 12.0451 13.8525 13.2226C14.2425 13.9051 14.01 14.7751 13.3275 15.1651L12.03 15.9076C11.4375 16.2601 10.6725 16.0501 10.32 15.4576L10.2375 15.3151C9.5625 14.1376 8.4525 14.1376 7.77 15.3151L7.6875 15.4576C7.335 16.0501 6.57 16.2601 5.9775 15.9076L4.68 15.1651C3.9975 14.7751 3.765 13.8976 4.155 13.2226C4.8375 12.0451 4.2825 11.0851 2.925 11.0851C2.1375 11.0851 1.5 10.4401 1.5 9.66007Z" stroke="currentColor" stroke-width="1.1875" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h6>settings</h6>
                </a>

            </div>

            <div class="account-control">
                <div class="account-info-card">
                    <div class="avatar-img">
                        <img src="{{ asset('assets/img/avatar.png') }}" alt="avatar icon">
                    </div>
                    <div class="avatar-info">
                        <h2>tee godwin</h2>
                        <h6>toksgodwin@gmail.com</h6>
                    </div>
                </div>

                <div class="setting-dropdown general-pop-opt">
                    <i class="fa fa-ellipsis-v open-drops" aria-hidden="true"></i>
                    <div class="side-dropdown">
                        <ul class="list-unstyled">
                            <li><a href="{{ url('profile') }}">Profile</a></li>
                            <li><a href="{{ url('account') }}">Account</a></li>
                            <li><a href="#" id="logout-dropdown">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>

        <div class="admin-body">
            <div class="sidebar-placeholder"></div>
            <div class="admin-content">
                <nav class="nav-page-title">
                    <div class="title-section">
                        <h1>@yield('nav_title')</h1>
                        <h6>@yield('nav_desc')</h6>
                    </div>

                    <!-- notification icon -->
                    <div class="titled-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.53001 14.77C2.31701 16.164 3.26801 17.131 4.43201 17.613C8.89501 19.463 15.105 19.463 19.568 17.613C20.732 17.131 21.683 16.163 21.47 14.77C21.34 13.913 20.693 13.2 20.214 12.503C19.587 11.579 19.525 10.572 19.524 9.5C19.525 5.358 16.157 2 12 2C7.843 2 4.47501 5.358 4.47501 9.5C4.47501 10.572 4.41301 11.58 3.78501 12.503C3.30701 13.2 2.66101 13.913 2.53001 14.77Z" stroke="#868C98" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 19C8.458 20.725 10.076 22 12 22C13.925 22 15.541 20.725 16 19" stroke="#868C98" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <!-- <form action="{{ route('logout') }}" method="post" hidden>
                            @csrf
                            <button id="logout">logout</button>
                        </form> -->
                        <!-- <a href="#" id="logout-btn">
                            <ion-icon name="power-outline" class="title-icon"></ion-icon>
                        </a> -->
                    </div>
                </nav>

                <div class="content-section">
                    @if(!Request::is('home'))
                    <div class="back-ctrl">
                        <ion-icon name="chevron-back-outline"></ion-icon>
                        <a href="#" id="backHistory">back</a>
                        <div class="lin-back"></div>
                        <h5>@yield('backname')</h5>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>

    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        $('#logout-btn, #logout-dropdown').click(function () {
            $('#logout').click();
        });

        var t = 0;
        $('.iconic-size-ctrl').click(function () {
            var idBox = $(this).attr('data-resize');
            $('#' + idBox).toggleClass('active');
            if (t == 0) {
                $(this).attr('name', 'chevron-down-outline');
                t = 1;
            } else {
                $(this).attr('name', 'chevron-up-outline');
                t = 0;
            }
        });

        $('.general-pop-opt').click(function (e) {
            const isTrigger = $(e.target).closest('.general-pop-opt');
            if (isTrigger.length) {
                const menu = isTrigger.find('.side-dropdown');
                menu.toggleClass('show');
            }
        });

        @if(Request::is('home'))

        Chart.register(ChartDataLabels);

        const ctx = document.getElementById('utilizationChart').getContext('2d');
        const utilizationChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        type: 'bar',
                        label: '2021 Utilization (%)',
                        data: [69, 17, 32, 20, 41, 11, 57, 15, 57, 52, 92, 42],
                        backgroundColor: 'rgba(128,128,128,0.6)',
                        borderColor: 'rgba(128,128,128,1)',
                        borderWidth: 1,
                        datalabels: {
                            anchor: 'end',
                            align: 'start',
                            offset: -30,
                            color: '#b3b3b3',
                            font: {
                                weight: 400,
                                size: 18,
                                family: 'Nunito'
                            }
                        }
                    },
                    {
                        type: 'line',
                        label: '2021 Trend',
                        data: [78, 61, 73, 69, 70, 86, 93, 70, 72, 74, 95, 66],
                        borderColor: '#3cc185',
                        backgroundColor: 'transparent',
                        tension: 0.5,
                        fill: false,
                        pointRadius: 4,
                        borderWidth: 1,
                        pointBackgroundColor: '#3cc185',
                        datalabels: {
                            display: false
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    datalabels: {
                        font: {
                            size: 12
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        @endif

        $('#backHistory').click(function (e) {
            e.preventDefault();
            window.history.back();
        });
    </script>

    @yield('local_js')
</body>

</html>

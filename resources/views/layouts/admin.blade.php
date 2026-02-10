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
    @yield('local_css')
</head>

<body>

    <section class="admin-parent">
        {{-- sidebar section --}}
        <aside class="sidebar-section">
            <div class="logo-divide">
<<<<<<< HEAD
                <img src="{{ asset('assets/img/logo-white.svg') }}" class="large-logo" alt="large size">
=======
                <img src="{{ asset('assets/img/light.png') }}" class="large-logo" alt="large size">
>>>>>>> 3c15039e2c70c3438132f63d031d6798556f25a4
                <img src="{{ asset('assets/img/favicon.png') }}" class="small-logo" alt="small size">
            </div>
            <div class="main-menu">
                <h6 class="sidebar-header">main menu</h6>
                <a href="{{ url('/home') }}" class="icon-link {{ Request::is('home') ? 'active' : '' }}">
                    <ion-icon name="calendar-outline" class="icon-ctrl"></ion-icon>
                    <h6>overview</h6>
                </a>
                <a href="{{ url('equipment') }}" class="icon-link  {{ Request::is('equipment') ? 'active' : '' }}">
                    <ion-icon name="boat-outline" class="icon-ctrl"></ion-icon>
                    <h6>equipment</h6>
                </a>
                <a href="{{ url('maintenance') }}" class="icon-link {{ Request::is('maintenance') ? 'active' : '' }}">
                    <ion-icon name="hammer-outline" class="icon-ctrl"></ion-icon>
                    <h6>maintenance</h6>
                </a>
                <a href="{{ url('users') }}" class="icon-link {{ Request::is('users') ? 'active' : '' }}">
                    <ion-icon name="people-outline" class="icon-ctrl"></ion-icon>
                    <h6>users</h6>
                </a>
                <a href="{{ url('reports') }}" class="icon-link {{ Request::is('reports') ? 'active' : '' }}">
                    <ion-icon name="bar-chart-outline" class="icon-ctrl"></ion-icon>
                    <h6>reports</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="settings-outline" class="icon-ctrl"></ion-icon>
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
                            <li><a href="#">Profile</a></li>
                            <li><a href="#">Account</a></li>
                            <li><a href="#">Logout</a></li>
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

                    <div class="titled-icon">
                        <ion-icon name="notifications-outline" class="title-icon"></ion-icon>
                        <form action="{{ route('logout') }}" method="post" hidden>
                            @csrf
                            <button id="logout">logout</button>
                        </form>
                        <a href="#" id="logout-btn">
                            <ion-icon name="power-outline" class="title-icon"></ion-icon>
                        </a>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        $('#logout-btn').click(function() {
            $('#logout').click();
        });
        var t = 0;
        $('.iconic-size-ctrl').click(function() {
            var idBox = $(this).attr('data-resize');
            $('#' + idBox).toggleClass('active');
            if (t == 0) {
                $(this).attr('name', 'chevron-down-outline');
                t = 1
            } else {
                $(this).attr('name', 'chevron-up-outline');
                t = 0
            }
        });
        

        $('.general-pop-opt').click(function(e) {
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
                datasets: [{
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

        $('#backHistory').click(function(e){
            e.preventDefault();
            window.history.back();
        })
    </script>

    @yield('local_js')
</body>

</html>

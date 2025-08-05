<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }} | Admin Dashboard</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>

</head>

<body>

    <section class="admin-parent">
        {{-- sidebar section --}}
        <aside class="sidebar-section">
            <div class="logo-divide">
                <img src="{{ asset('assets/img/light.png') }}" alt="">
            </div>
            <div class="main-menu">
                <h6 class="sidebar-header">main menu</h6>
                <a href="#" class="icon-link active">
                    <ion-icon name="calendar-outline" class="icon-ctrl"></ion-icon>
                    <h6>overview</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="boat-outline" class="icon-ctrl"></ion-icon>
                    <h6>equipment</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="hammer-outline" class="icon-ctrl"></ion-icon>
                    <h6>maintenance</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="people-outline" class="icon-ctrl"></ion-icon>
                    <h6>users</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="bar-chart-outline" class="icon-ctrl"></ion-icon>
                    <h6>reports</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="settings-outline" class="icon-ctrl"></ion-icon>
                    <h6>settings</h6>
                </a>
            </div>
        </aside>

        <div class="admin-body">
            <div class="sidebar-placeholder"></div>
            <div class="admin-content">
                <nav class="nav-page-title">
                    <div class="title-section">
                        <h1>overview</h1>
                        <h6>overview of your Equipment Management System</h6>
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
                    <div class="quick-stats-section">
                        <h4 class="body-title">quick stats</h4>
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="status-parent">
                                        <div class="status-card">
                                            <div class="status-bar">
                                                <div class="stats-icon increase">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                    <h6>20%</h6>
                                                </div>
                                                <a href="#" class="details-link">
                                                    <h6>View details</h6>
                                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            <div class="status-content">
                                                <div class="sc-icon">
                                                    <ion-icon name="boat-outline" class="sc-img"></ion-icon>
                                                </div>
                                                <div class="sc-desc">
                                                    <h6>total equipment</h6>
                                                    <h2>322</h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="status-parent">
                                        <div class="status-card">
                                            <div class="status-bar">
                                                <div class="stats-icon increase">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                    <h6>20%</h6>
                                                </div>
                                                <a href="#" class="details-link">
                                                    <h6>View details</h6>
                                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            <div class="status-content">
                                                <div class="sc-icon">
                                                    <ion-icon name="settings-outline" class="sc-img"></ion-icon>
                                                </div>
                                                <div class="sc-desc">
                                                    <h6>active assets</h6>
                                                    <h2>17</h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="status-parent">
                                        <div class="status-card">
                                            <div class="status-bar">
                                                <div class="stats-icon increase">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                    <h6>20%</h6>
                                                </div>
                                                <a href="#" class="details-link">
                                                    <h6>View details</h6>
                                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            <div class="status-content">
                                                <div class="sc-icon">
                                                    <ion-icon name="clipboard-outline" class="sc-img"></ion-icon>
                                                </div>
                                                <div class="sc-desc">
                                                    <h6>pending maintenance</h6>
                                                    <h2>3</h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="status-parent">
                                        <div class="status-card">
                                            <div class="status-bar">
                                                <div class="stats-icon increase">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                    <h6>20%</h6>
                                                </div>
                                                <a href="#" class="details-link">
                                                    <h6>View details</h6>
                                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            <div class="status-content">
                                                <div class="sc-icon">
                                                    <ion-icon name="boat-outline" class="sc-img"></ion-icon>
                                                </div>
                                                <div class="sc-desc">
                                                    <h6>total equipment</h6>
                                                    <h2>322</h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 br-box">
                        <div class="br-titled">
                            <h2>Equipment Utilization</h2>
                        </div>  
                        <canvas id="utilizationChart"></canvas>
                    </div>

                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mt-5 br-box slidingBox active" id="utilizeBox">
                                    <div class="br-titled">
                                        <div class="icon-titled">
                                            <div class="icon-shaped">
                                                <ion-icon name="boat-outline" class="iconic"></ion-icon>
                                            </div>
                                            <h2>Equipment Utilization</h2>
                                        </div>
                                        <ion-icon name="chevron-up-outline" class="iconic-size-ctrl" data-resize="utilizeBox"></ion-icon>
                                    </div>

                                    <div class="equipment-card mt-4">
                                        <div class="ec-nav">
                                            <div class="ec-nav-label">
                                                <ion-icon name="boat-outline" class="ec-nav-icon"></ion-icon>
                                                <h6>Generator Unit A</h6>
                                            </div>
                                            <div class="ec-menu">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </div>
                                        </div>

                                        <div class="ec-item">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>
                                            <h6>Next Maintenance: 24/08/2025</h6>
                                        </div>
                                        <div class="ec-item">
                                            <ion-icon name="cog-outline" class="ec-item-icon"></ion-icon>
                                            <h6>Utilization and Usage: 32%</h6>
                                        </div>
                                        <label class="ec-label">operational</label>
                                    </div>

                                    <div class="ec-btn-group">
                                        <div class="ec-btn-box">
                                            <a href="javascript:void(0)" class="btn ec-btn">
                                                <ion-icon name="time-outline"></ion-icon>
                                                view activity logs
                                            </a>
                                        </div>
                                        <div class="ec-btn-box">
                                            <a href="javascript:void(0)" class="btn ec-btn">
                                                <ion-icon name="checkmark-outline"></ion-icon>
                                                view maintenance reports
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="equipment-card mt-4">
                                        <div class="ec-nav">
                                            <div class="ec-nav-label">
                                                <ion-icon name="boat-outline" class="ec-nav-icon default"></ion-icon>
                                                <h6>HVAC SYSTEM 1</h6>
                                            </div>
                                            <div class="ec-menu">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="ec-item">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>
                                            <h6>Next Maintenance: 24/08/2025</h6>
                                        </div>
                                        <div class="ec-item">
                                            <ion-icon name="cog-outline" class="ec-item-icon"></ion-icon>
                                            <h6>Utilization and Usage: 32%</h6>
                                        </div>
                                        <label class="ec-label warning">maintenance</label>
                                    </div>
                                    
                                    <div class="equipment-card mt-4">
                                        <div class="ec-nav">
                                            <div class="ec-nav-label">
                                                <ion-icon name="boat-outline" class="ec-nav-icon default"></ion-icon>
                                                <h6>Pump Station 1</h6>
                                            </div>
                                            <div class="ec-menu">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="ec-item">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>
                                            <h6>Next Maintenance: 24/08/2025</h6>
                                        </div>
                                        <div class="ec-item">
                                            <ion-icon name="cog-outline" class="ec-item-icon"></ion-icon>
                                            <h6>Utilization and Usage: 32%</h6>
                                        </div>
                                        <label class="ec-label danger">critical</label>
                                    </div>
                                    
                                    <div class="ec-btn-group">
                                        <div class="ec-btn-box w-100">
                                            <a href="javascript:void(0)" class="btn ec-btn">
                                                view all
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="mt-5 br-box slidingBox active" id="utilizeBox">
                                    <div class="br-titled">
                                        <div class="icon-titled">
                                            <div class="icon-shaped">
                                                <ion-icon name="alert-circle-outline"></ion-icon>
                                            </div>
                                            <h2>maintenance alerts</h2>
                                        </div>
                                        <ion-icon name="chevron-up-outline" class="iconic-size-ctrl" data-resize="utilizeBox"></ion-icon>
                                    </div>

                                    <div class="equipment-card danger mt-4">
                                        <div class="ec-nav">
                                            <div class="ec-nav-label">
                                                <ion-icon name="boat-outline" class="ec-nav-icon danger"></ion-icon>
                                                <h6>Generator Unit A</h6>
                                            </div>
                                            <div class="ec-menu">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </div>
                                        </div>

                                        <div class="ec-item">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>
                                            <h6>Next Maintenance: 24/08/2025</h6>
                                        </div>
                                        <div class="ec-item">
                                            <ion-icon name="cog-outline" class="ec-item-icon"></ion-icon>
                                            <h6>Utilization and Usage: 32%</h6>
                                        </div>
                                        <label class="ec-label danger">critical</label>
                                    </div>

                                    <div class="ec-btn-group">
                                        <div class="ec-btn-box">
                                            <a href="javascript:void(0)" class="btn ec-btn">
                                                <ion-icon name="time-outline"></ion-icon>
                                                schedule repair
                                            </a>
                                        </div>
                                        <div class="ec-btn-box">
                                            <a href="javascript:void(0)" class="btn ec-btn">
                                                <ion-icon name="checkmark-outline"></ion-icon>
                                                mark as resolved
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="equipment-card warning mt-4">
                                        <div class="ec-nav">
                                            <div class="ec-nav-label">
                                                <ion-icon name="boat-outline" class="ec-nav-icon warning default"></ion-icon>
                                                <h6>HVAC SYSTEM 1</h6>
                                            </div>
                                            <div class="ec-menu">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="ec-item">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>
                                            <h6>Next Maintenance: 24/08/2025</h6>
                                        </div>
                                        <div class="ec-item">
                                            <ion-icon name="cog-outline" class="ec-item-icon"></ion-icon>
                                            <h6>Utilization and Usage: 32%</h6>
                                        </div>
                                        <label class="ec-label warning">maintenance</label>
                                    </div>
                                    
                                    <div class="equipment-card primary mt-4">
                                        <div class="ec-nav">
                                            <div class="ec-nav-label">
                                                <ion-icon name="boat-outline" class="ec-nav-icon primary"></ion-icon>
                                                <h6>Pump Station 1</h6>
                                            </div>
                                            <div class="ec-menu">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="ec-item">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>
                                            <h6>Next Maintenance: 24/08/2025</h6>
                                        </div>
                                        <div class="ec-item">
                                            <ion-icon name="cog-outline" class="ec-item-icon"></ion-icon>
                                            <h6>Utilization and Usage: 32%</h6>
                                        </div>
                                        <label class="ec-label primary">balanced</label>
                                    </div>
                                    
                                    <div class="ec-btn-group">
                                        <div class="ec-btn-box w-100">
                                            <a href="javascript:void(0)" class="btn ec-btn">
                                                view all
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        $('#logout-btn').click(function() {
            $('#logout').click();
        });
        var t = 0;
        $('.iconic-size-ctrl').click(function(){
            var idBox = $(this).attr('data-resize');
            $('#'+idBox).toggleClass('active');
            if(t == 0){
                $(this).attr('name','chevron-down-outline');
                t = 1
            }else{
                $(this).attr('name','chevron-up-outline');
                t = 0
            }
        });

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
    </script>
</body>

</html>

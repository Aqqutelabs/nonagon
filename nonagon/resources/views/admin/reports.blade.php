@extends('layouts.admin')

@section('nav_title','Reports')
@section('nav_desc','Generate detailed reports and analyze performance metrics')
@section('backname','Reports')

@section('local_css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/1.13.6/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/maintenance.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/reports.css') }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@endsection

@section('content')
<div class="row mt-2">
    <div class="col-lg-12">
        <ul class="nav nav-tabs listings-section" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="maintain-tab" data-bs-toggle="tab" data-bs-target="#maintain" type="button" role="tab" aria-controls="maintain" aria-selected="true">
                    <ion-icon name="build-outline"></ion-icon> Maintenance Reports
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tableview-tab" data-bs-toggle="tab" data-bs-target="#equipmentstats" type="button" role="tab" aria-controls="equipmentstats" aria-selected="false">
                    <ion-icon name="boat-outline"></ion-icon>
                    Equipment Status
                </button>
            </li>
            <li class="nav-item" role="downtime">
                <button class="nav-link" id="downtime-tab" data-bs-toggle="tab" data-bs-target="#downtime" type="button" role="tab" aria-controls="downtime" aria-selected="false">
                    <ion-icon name="cloud-download-outline"></ion-icon>
                    Downtime Analytics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="costanalysis-tab" data-bs-toggle="tab" data-bs-target="#costanalysis" type="button" role="tab" aria-controls="costanalysis" aria-selected="false">
                    <ion-icon name="pie-chart-outline"></ion-icon>
                    Cost Analysis
                </button>
            </li>
        </ul>
    </div>
</div>
<div class="qd-tabcontent tab-content">
    <div class="tab-pane fade show active" id="maintain" role="tabpanel" aria-labelledby="maintain-tab"> 
        <div class="quick-stats-section mt-4">
            <h4 class="body-title">Key metrics</h4>
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
                                        <ion-icon name="boat-outline" class="sc-img danger"></ion-icon>
                                    </div>
                                    <div class="sc-desc">
                                        <h6>total tasks completed</h6>
                                        <h2>87</h2>
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
                                        <ion-icon name="settings-outline" class="sc-img warning"></ion-icon>
                                    </div>
                                    <div class="sc-desc">
                                        <h6>average completion time</h6>
                                        <h2>4.2 hrs</h2>
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
                                        <ion-icon name="clipboard-outline" class="sc-img primary"></ion-icon>
                                    </div>
                                    <div class="sc-desc">
                                        <h6>Prevention vs Correctional</h6>
                                        <h2>75%</h2>
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
                                        <ion-icon name="alert-circle-outline" class="sc-img success"></ion-icon>
                                    </div>
                                    <div class="sc-desc">
                                        <h6>Tasks Overdue</h6>
                                        <h2>4</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-container mt-5">
            <div class="chart-bordered mb-4">
                <div class="performance-header">
                    <div class="performance-title">Performance Trends</div>
                    <a href="#" class="more-link">MORE</a>
                </div>

                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color legend-2021"></div>
                            <span>2021</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-2022"></div>
                            <span>2022</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-line"></div>
                            <span>2021</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Reports Section -->
            <div class="quick-reports-header">
                <h4 class="quick-reports-title">Quick Reports</h4>
                <button class="days-filter">
                    Last 30 days
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div class="reports-grid">
                <div class="report-card">
                    <div class="report-icon icon-weekly">
                        <i class="far fa-file-alt"></i>
                    </div>
                    <div class="report-title">Weekly Summary</div>
                    <p class="report-subtitle">Generate 7 days overview</p>
                </div>

                <div class="report-card">
                    <div class="report-icon icon-critical">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="report-title">Critical Issues</div>
                    <p class="report-subtitle">High Priority Alerts</p>
                </div>

                <div class="report-card">
                    <div class="report-icon icon-performance">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="report-title">Performance Analysis</div>
                    <p class="report-subtitle">Monthly Analysis</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-pane fade" id="equipmentstats" role="tabpanel" aria-labelledby="equipmentstats-tab">
        ...
    </div>
    
    <div class="tab-pane fade" id="equipmentstats" role="tabpanel" aria-labelledby="equipmentstats-tab">
        ...
    </div>
    
    <div class="tab-pane fade" id="equipmentstats" role="tabpanel" aria-labelledby="equipmentstats-tab">
        ...
    </div>
</div>
@endsection

@section('local_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/1.13.6/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/1.13.6/dataTables.bootstrap5.min.js"></script>
<script>
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    const chartData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
        datasets: [
            {
                label: '2021',
                data: [70, 18, 30, 40, 42, 15, 58, 18, 58, 52, 90, 42],
                backgroundColor: '#6f42c1',
                borderColor: '#6f42c1',
                type: 'bar',
                yAxisID: 'y'
            },
            {
                label: '2022',
                data: [42, 48, 32, 82, 18, 18, 52, 35, 92, 92, 55, 18],
                backgroundColor: '#20c997',
                borderColor: '#20c997',
                type: 'bar',
                yAxisID: 'y'
            },
            {
                label: '2021 Line',
                data: [78, 62, 75, 75, 75, 88, 98, 75, 75, 82, 82, 62],
                borderColor: '#ffc107',
                backgroundColor: 'transparent',
                type: 'line',
                tension: 0.4,
                pointBackgroundColor: '#ffc107',
                pointBorderColor: '#ffc107',
                pointRadius: 4,
                yAxisID: 'y1'
            }
        ]
    };

    const config = {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    max: 100,
                    grid: {
                        color: '#f8f9fa'
                    },
                    ticks: {
                        stepSize: 20,
                        color: '#6c757d',
                        font: {
                            size: 12
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        stepSize: 20,
                        color: '#6c757d',
                        font: {
                            size: 12
                        }
                    }
                }
            },
            interaction: {
                intersect: false
            }
        }
    };

    new Chart(ctx, config);

    // Add click functionality to report cards
    document.querySelectorAll('.report-card').forEach(card => {
        card.addEventListener('click', function() {
            console.log('Report card clicked:', this.querySelector('.report-title').textContent);
        });
    });

    // Add functionality to days filter
    document.querySelector('.days-filter').addEventListener('click', function() {
        console.log('Days filter clicked');
    });
</script>
@endsection
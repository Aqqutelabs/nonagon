@extends('layouts.admin')

@section('nav_title','Overview')
@section('nav_desc','overview of your equipment management system')

@section('content')
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
            <div class="mt-5 br-box slidingBox active" id="maintenanceBox">
                <div class="br-titled">
                    <div class="icon-titled">
                        <div class="icon-shaped">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                        </div>
                        <h2>maintenance alerts</h2>
                    </div>
                    <ion-icon name="chevron-up-outline" class="iconic-size-ctrl" data-resize="maintenanceBox"></ion-icon>
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
                    <label class="ec-label primary">operational</label>
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
@endsection
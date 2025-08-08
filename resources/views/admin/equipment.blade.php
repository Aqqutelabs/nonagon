@extends('layouts.admin')
@section('content')

    <div class="row">
        <div class="col-lg-6">
            <ul class="nav nav-tabs qd-bars" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="equipment-tab" data-bs-toggle="tab" data-bs-target="#equipment" type="button"
                        role="tab" aria-controls="equipment" aria-selected="true">my equipment</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="listings-tab" data-bs-toggle="tab" data-bs-target="#listings" type="button"
                        role="tab" aria-controls="listings" aria-selected="false">my listings</button>
                </li>
            </ul>
        </div>
        <div class="col-lg-6">
            <div class="qd-search-equipment">

            </div>
        </div>
    </div>
    <div class="qd-tabcontent tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="equipment" role="tabpanel" aria-labelledby="equipment-tab">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="qd-equipment-card">
                            <div class="qd-card-header">
                                <img src="{{ asset('assets/img/1.jpg') }}" alt="Mac Semi Truck" class="qd-equipment-image">
                            </div>

                            <h3 class="qd-equipment-title">Mac Bulldozer 12DH</h3>
                            <div class="qd-equipment-price">$120,000/hr</div>

                            <div class="qd-card-body">
                                
                                <div class="qd-equipment-details">
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Model:</span>
                                        <span class="qd-detail-value">GEN-NX 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Serial Number:</span>
                                        <span class="qd-detail-value">EPE-108X 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Utilization & Usage:</span>
                                        <span class="qd-detail-value">32%</span>
                                    </div>
                                    
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Next Maintenance:</span>
                                        <span class="qd-detail-value">24/08/2025</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Last Maintenance:</span>
                                        <span class="qd-detail-value">08/04/2025</span>
                                    </div>
                                    
                                    <div class="qd-status-badge">Operational</div>
                                </div>

                            </div>

                            <div class="qd-card-actions">
                                <button class="qd-action-btn">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    View Details
                                </button>
                                <button class="qd-action-btn">
                                    <ion-icon name="build-outline"></ion-icon>
                                    Schedule Repair
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="qd-equipment-card">
                            <div class="qd-card-header">
                                <img src="{{ asset('assets/img/2.jpg') }}" alt="Mac Semi Truck" class="qd-equipment-image">
                            </div>

                            <h3 class="qd-equipment-title">Mac Bulldozer 12DH</h3>
                            <div class="qd-equipment-price">$120,000/hr</div>

                            <div class="qd-card-body">
                                
                                <div class="qd-equipment-details">
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Model:</span>
                                        <span class="qd-detail-value">GEN-NX 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Serial Number:</span>
                                        <span class="qd-detail-value">EPE-108X 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Utilization & Usage:</span>
                                        <span class="qd-detail-value">32%</span>
                                    </div>
                                    
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Next Maintenance:</span>
                                        <span class="qd-detail-value">24/08/2025</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Last Maintenance:</span>
                                        <span class="qd-detail-value">08/04/2025</span>
                                    </div>
                                    
                                    <div class="qd-status-badge">Operational</div>
                                </div>

                            </div>

                            <div class="qd-card-actions">
                                <button class="qd-action-btn">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    View Details
                                </button>
                                <button class="qd-action-btn">
                                    <ion-icon name="build-outline"></ion-icon>
                                    Schedule Repair
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="qd-equipment-card">
                            <div class="qd-card-header">
                                <img src="{{ asset('assets/img/3.jpg') }}" alt="Mac Semi Truck" class="qd-equipment-image">
                            </div>

                            <h3 class="qd-equipment-title">Mac Bulldozer 12DH</h3>
                            <div class="qd-equipment-price">$120,000/hr</div>

                            <div class="qd-card-body">
                                
                                <div class="qd-equipment-details">
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Model:</span>
                                        <span class="qd-detail-value">GEN-NX 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Serial Number:</span>
                                        <span class="qd-detail-value">EPE-108X 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Utilization & Usage:</span>
                                        <span class="qd-detail-value">32%</span>
                                    </div>
                                    
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Next Maintenance:</span>
                                        <span class="qd-detail-value">24/08/2025</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Last Maintenance:</span>
                                        <span class="qd-detail-value">08/04/2025</span>
                                    </div>
                                    
                                    <div class="qd-status-badge">Operational</div>
                                </div>

                            </div>

                            <div class="qd-card-actions">
                                <button class="qd-action-btn">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    View Details
                                </button>
                                <button class="qd-action-btn">
                                    <ion-icon name="build-outline"></ion-icon>
                                    Schedule Repair
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="qd-equipment-card">
                            <div class="qd-card-header">
                                <img src="{{ asset('assets/img/4.jpg') }}" alt="Mac Semi Truck" class="qd-equipment-image">
                            </div>

                            <h3 class="qd-equipment-title">Mac Bulldozer 12DH</h3>
                            <div class="qd-equipment-price">$120,000/hr</div>

                            <div class="qd-card-body">
                                
                                <div class="qd-equipment-details">
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Model:</span>
                                        <span class="qd-detail-value">GEN-NX 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Serial Number:</span>
                                        <span class="qd-detail-value">EPE-108X 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Utilization & Usage:</span>
                                        <span class="qd-detail-value">32%</span>
                                    </div>
                                    
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Next Maintenance:</span>
                                        <span class="qd-detail-value">24/08/2025</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Last Maintenance:</span>
                                        <span class="qd-detail-value">08/04/2025</span>
                                    </div>
                                    
                                    <div class="qd-status-badge">Operational</div>
                                </div>

                            </div>

                            <div class="qd-card-actions">
                                <button class="qd-action-btn">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    View Details
                                </button>
                                <button class="qd-action-btn">
                                    <ion-icon name="build-outline"></ion-icon>
                                    Schedule Repair
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="qd-equipment-card">
                            <div class="qd-card-header">
                                <img src="{{ asset('assets/img/5.jpg') }}" alt="Mac Semi Truck" class="qd-equipment-image">
                            </div>

                            <h3 class="qd-equipment-title">Mac Bulldozer 12DH</h3>
                            <div class="qd-equipment-price">$120,000/hr</div>

                            <div class="qd-card-body">
                                
                                <div class="qd-equipment-details">
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Model:</span>
                                        <span class="qd-detail-value">GEN-NX 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Serial Number:</span>
                                        <span class="qd-detail-value">EPE-108X 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Utilization & Usage:</span>
                                        <span class="qd-detail-value">32%</span>
                                    </div>
                                    
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Next Maintenance:</span>
                                        <span class="qd-detail-value">24/08/2025</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Last Maintenance:</span>
                                        <span class="qd-detail-value">08/04/2025</span>
                                    </div>
                                    
                                    <div class="qd-status-badge">Operational</div>
                                </div>

                            </div>

                            <div class="qd-card-actions">
                                <button class="qd-action-btn">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    View Details
                                </button>
                                <button class="qd-action-btn">
                                    <ion-icon name="build-outline"></ion-icon>
                                    Schedule Repair
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="qd-equipment-card">
                            <div class="qd-card-header">
                                <img src="{{ asset('assets/img/6.jpg') }}" alt="Mac Semi Truck" class="qd-equipment-image">
                            </div>

                            <h3 class="qd-equipment-title">Mac Bulldozer 12DH</h3>
                            <div class="qd-equipment-price">$120,000/hr</div>

                            <div class="qd-card-body">
                                
                                <div class="qd-equipment-details">
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Model:</span>
                                        <span class="qd-detail-value">GEN-NX 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Serial Number:</span>
                                        <span class="qd-detail-value">EPE-108X 24D</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="settings-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Utilization & Usage:</span>
                                        <span class="qd-detail-value">32%</span>
                                    </div>
                                    
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Next Maintenance:</span>
                                        <span class="qd-detail-value">24/08/2025</span>
                                    </div>
                                    <div class="qd-detail-row">
                                        <span class="qd-detail-icon">
                                            <ion-icon name="build-outline"></ion-icon>
                                        </span>
                                        <span class="qd-detail-label">Last Maintenance:</span>
                                        <span class="qd-detail-value">08/04/2025</span>
                                    </div>
                                    
                                    <div class="qd-status-badge">Operational</div>
                                </div>

                            </div>

                            <div class="qd-card-actions">
                                <button class="qd-action-btn">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    View Details
                                </button>
                                <button class="qd-action-btn">
                                    <ion-icon name="build-outline"></ion-icon>
                                    Schedule Repair
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="listings" role="tabpanel" aria-labelledby="listings-tab">...</div>
    </div>
@endsection
@section('local_js')
@endsection

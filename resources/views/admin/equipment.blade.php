@extends('layouts.admin')
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-tabs qd-bars" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="equipment-tab" data-bs-toggle="tab" data-bs-target="#equipment"
                        type="button" role="tab" aria-controls="equipment" aria-selected="true">my equipment</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="listings-tab" data-bs-toggle="tab" data-bs-target="#listings"
                        type="button" role="tab" aria-controls="listings" aria-selected="false">my listings</button>
                </li>
            </ul>
        </div>
        <div class="col-lg-8">
            <div class="qd-search-equipment">
                <div class="other-controls">
                    <div class="search-box">
                        <form action="#" method="post">
                            <div class="input-group mb-3 qd-search">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <ion-icon name="search-outline" aria-label="search outline" role="img"
                                            class="md hydrated"></ion-icon>
                                    </span>
                                </div>
                                <input type="text" id="search-form" placeholder="Search" class="form-control"
                                    aria-label="Amount (to the nearest dollar)">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <ion-icon name="funnel-outline" aria-label="funnel outline" role="img"
                                            class="md hydrated"></ion-icon>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div>
                        <button class="qd-button btn" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            <ion-icon name="add-outline"></ion-icon>
                            add new equipment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal add equipment -->
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header qp-modal-header">
                            <div class="icon-modal-title">
                                <div class="modal-desc-icon">
                                    <ion-icon name="newspaper-outline"></ion-icon>
                                </div>
                                <div class="modal-desc">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Add New Equipment</b></h1>
                                    <h6>Add a new equipment to your assets</h6>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form action="#">
                                @csrf
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Equipment Model
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="eqp_name" name="eqp_name" placeholder="Enter Equipment Name">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Year of Production
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="year" name="year" placeholder="Enter Year Of Production">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Registration Number
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="reg_no" name="reg_no" placeholder="Enter Registration Number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Equipment ID
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="equipment_id" name="equipment_id" placeholder="Enter ID Number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Industry
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <select name="industry" id="industry" required class="form-control">
                                                    <option value="">Select Industry</option>
                                                    <option value="Industry 1">Industry 1</option>
                                                    <option value="Industry 2">Industry 2</option>
                                                    <option value="Industry 3">Industry 3</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Equipment Description (optional)
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <textarea name="description" class="form-control" id="description" cols="30" rows="4" placeholder="Write a sentence ..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            State/City of Operation
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="city_of_operation" name="city_of_operation" placeholder="State/City Of Operation">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            Upload Document
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="upload-document">
                                                <div class="file-icon">
                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                </div>
                                                <div class="document-box">
                                                    click to upload an image or document
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                        <div class="col-lg-12 p-4">
                            <div class="row">
                                <div class="col-lg-6 d-grid">
                                    <a href="javascript:void(0)" class="btn qd-button" data-bs-dismiss="modal">
                                        Cancel
                                    </a>
                                </div>
                                <div class="col-lg-6 d-grid">
                                    <a href="javascript:void(0)" class="btn qd-button active">
                                        <ion-icon name="add-outline"></ion-icon>
                                        Add New Equipment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                <img src="{{ asset('assets/img/2.jpg') }}" alt="Mac Semi Truck"
                                    class="qd-equipment-image">
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
                                <img src="{{ asset('assets/img/3.jpg') }}" alt="Mac Semi Truck"
                                    class="qd-equipment-image">
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
                                <img src="{{ asset('assets/img/4.jpg') }}" alt="Mac Semi Truck"
                                    class="qd-equipment-image">
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
                                <img src="{{ asset('assets/img/5.jpg') }}" alt="Mac Semi Truck"
                                    class="qd-equipment-image">
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
                                <img src="{{ asset('assets/img/6.jpg') }}" alt="Mac Semi Truck"
                                    class="qd-equipment-image">
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

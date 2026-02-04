@extends('layouts.admin')

@section('nav_title','Equipment')
@section('nav_desc','Monitor and manage all equipment across your facilities')
@section('backname','Equipment')

@section('local_css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/equipment-table.css') }}">
@endsection
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
                    <div class="table-search-box">
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
                                                    <i class="fa fa-file-text" aria-hidden="true"></i>
                                                </div>
                                                <div class="document-box">
                                                    <a href="#">Click to upload</a> an image or document
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
    <div class="qd-tabcontent tab-content">
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
        <div class="tab-pane fade" id="listings" role="tabpanel" aria-labelledby="listings-tab">

            <ul class="nav nav-tabs listings-section mt-5" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-bookings-tab" data-bs-toggle="tab" data-bs-target="#all-bookings" type="button" role="tab" aria-controls="all-bookings" aria-selected="true">
                        <ion-icon name="layers-outline"></ion-icon> All Bookings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="active-bookings-tab" data-bs-toggle="tab" data-bs-target="#active-bookings" type="button" role="tab" aria-controls="active-bookings" aria-selected="false">
                        <ion-icon name="newspaper-outline"></ion-icon>
                        Active Bookings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-bookings-tab" data-bs-toggle="tab" data-bs-target="#pending-bookings" type="button" role="tab" aria-controls="pending-bookings" aria-selected="false">
                        <ion-icon name="checkbox-outline"></ion-icon> Pending Bookings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-bookings-tab" data-bs-toggle="tab" data-bs-target="#completed-bookings" type="button" role="tab" aria-controls="completed-bookings" aria-selected="false">
                        <ion-icon name="folder-open-outline"></ion-icon> Completed Bookings
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="all-bookings" role="tabpanel" aria-labelledby="all-bookings-tab">
                    <div class="equipment-container">
                        <!-- Header Controls -->
                        <div class="table-controls">
                            <div class="left-controls">
                                <div class="search-box">
                                    <input type="text" id="customSearch" placeholder="Search here...">
                                    <span class="search-icon">
                                        <ion-icon name="search-outline"></ion-icon>
                                    </span>
                                </div>
                                
                                <button class="table-ctrl-btn">
                                    <ion-icon name="funnel-outline"></ion-icon>
                                    Filter
                                </button>
                            </div>
                            <div class="right-controls">
                                <button class="table-ctrl-btn">
                                    <ion-icon name="download-outline"></ion-icon>
                                    Export Data
                                </button>
                                <button class="table-ctrl-btn">
                                    <ion-icon name="share-social-outline"></ion-icon>
                                    Share
                                </button>
                            </div>
                        </div>
                        
                        <!-- DataTable -->
                        <table class="display equipmentTable">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Lessee</th>
                                    <th width="100">Lease Date</th>
                                    <th>Status</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge completed">completed</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                        
                        <!-- Footer Controls -->
                        <div class="footer-controls">
                            <div class="left-footer">
                                <div class="rows-per-page">
                                    Rows per Page:
                                    <select id="rowsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="right-footer">
                                <div class="go-to-page">
                                    Go to Page:
                                    <input type="number" id="pageInput" min="1" value="1">
                                    <button class="go-btn" onclick="goToPage()">Go</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="active-bookings" role="tabpanel" aria-labelledby="active-bookings-tab">
                    <div class="equipment-container">
                        <!-- Header Controls -->
                        <div class="table-controls">
                            <div class="left-controls">
                                <div class="search-box">
                                    <input type="text" id="customSearch" placeholder="Search here...">
                                    <span class="search-icon">
                                        <ion-icon name="search-outline"></ion-icon>
                                    </span>
                                </div>
                                
                                <button class="table-ctrl-btn">
                                    <ion-icon name="funnel-outline"></ion-icon>
                                    Filter
                                </button>
                            </div>
                            <div class="right-controls">
                                <button class="table-ctrl-btn">
                                    <ion-icon name="download-outline"></ion-icon>
                                    Export Data
                                </button>
                                <button class="table-ctrl-btn">
                                    <ion-icon name="share-social-outline"></ion-icon>
                                    Share
                                </button>
                            </div>
                        </div>
                        
                        <!-- DataTable -->
                        <table class="display equipmentTable">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Lessee</th>
                                    <th width="100">Lease Date</th>
                                    <th>Status</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge active">active</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge active">active</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                        
                        <!-- Footer Controls -->
                        <div class="footer-controls">
                            <div class="left-footer">
                                <div class="rows-per-page">
                                    Rows per Page:
                                    <select id="rowsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="right-footer">
                                <div class="go-to-page">
                                    Go to Page:
                                    <input type="number" id="pageInput" min="1" value="1">
                                    <button class="go-btn" onclick="goToPage()">Go</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pending-bookings" role="tabpanel" aria-labelledby="pending-bookings-tab">
                    <div class="equipment-container">
                        <!-- Header Controls -->
                        <div class="table-controls">
                            <div class="left-controls">
                                <div class="search-box">
                                    <input type="text" id="customSearch" placeholder="Search here...">
                                    <span class="search-icon">
                                        <ion-icon name="search-outline"></ion-icon>
                                    </span>
                                </div>
                                
                                <button class="table-ctrl-btn">
                                    <ion-icon name="funnel-outline"></ion-icon>
                                    Filter
                                </button>
                            </div>
                            <div class="right-controls">
                                <button class="table-ctrl-btn">
                                    <ion-icon name="download-outline"></ion-icon>
                                    Export Data
                                </button>
                                <button class="table-ctrl-btn">
                                    <ion-icon name="share-social-outline"></ion-icon>
                                    Share
                                </button>
                            </div>
                        </div>
                        
                        <!-- DataTable -->
                        <table class="display equipmentTable">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Lessee</th>
                                    <th width="100">Lease Date</th>
                                    <th>Status</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge pending">pending</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge pending">pending</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                        
                        <!-- Footer Controls -->
                        <div class="footer-controls">
                            <div class="left-footer">
                                <div class="rows-per-page">
                                    Rows per Page:
                                    <select id="rowsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="right-footer">
                                <div class="go-to-page">
                                    Go to Page:
                                    <input type="number" id="pageInput" min="1" value="1">
                                    <button class="go-btn" onclick="goToPage()">Go</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="completed-bookings" role="tabpanel" aria-labelledby="completed-bookings-tab">
                    <div class="equipment-container">
                        <!-- Header Controls -->
                        <div class="table-controls">
                            <div class="left-controls">
                                <div class="search-box">
                                    <input type="text" id="customSearch" placeholder="Search here...">
                                    <span class="search-icon">
                                        <ion-icon name="search-outline"></ion-icon>
                                    </span>
                                </div>
                                
                                <button class="table-ctrl-btn">
                                    <ion-icon name="funnel-outline"></ion-icon>
                                    Filter
                                </button>
                            </div>
                            <div class="right-controls">
                                <button class="table-ctrl-btn">
                                    <ion-icon name="download-outline"></ion-icon>
                                    Export Data
                                </button>
                                <button class="table-ctrl-btn">
                                    <ion-icon name="share-social-outline"></ion-icon>
                                    Share
                                </button>
                            </div>
                        </div>
                        
                        <!-- DataTable -->
                        <table class="display equipmentTable">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Lessee</th>
                                    <th width="100">Lease Date</th>
                                    <th>Status</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td>
                                        <span class="status-badge completed">completed</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge completed">completed</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAT 320D Excavator</td>
                                    <td>Gibraltar Construction</td>
                                    <td>Jun 25 - Jul 12 2025</td>
                                    <td><span class="status-badge completed">completed</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="qp-table-btn edit-btn" title="Edit">
                                                <ion-icon name="pencil-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn delete-btn" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                            <button class="qp-table-btn general-pop-opt" title="More">
                                                <ion-icon name="ellipsis-vertical-outline" class="open-drops"></ion-icon>
                                                <div class="side-dropdown options">
                                                    <ul class="list-unstyled">
                                                        <li><a href="#">Menu 1</a></li>
                                                        <li><a href="#">Menu 2</a></li>
                                                        <li><a href="#">Menu 3</a></li>
                                                    </ul>
                                                </div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                        
                        <!-- Footer Controls -->
                        <div class="footer-controls">
                            <div class="left-footer">
                                <div class="rows-per-page">
                                    Rows per Page:
                                    <select id="rowsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="right-footer">
                                <div class="go-to-page">
                                    Go to Page:
                                    <input type="number" id="pageInput" min="1" value="1">
                                    <button class="go-btn" onclick="goToPage()">Go</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('local_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('.equipmentTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            searching: false,
            info: true,
            pagingType: "full_numbers",
            language: {
                info: "Showing _START_-_END_ of _TOTAL_",
                paginate: {
                    first: "<<",
                    last: ">>",
                    next: ">",
                    previous: "<"
                }
            },
            drawCallback: function(settings) {
                var api = this.api();
                var page = api.page.info();
                $('#pageInput').val(page.page + 1);
            }
        });
        
        $('#customSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
        
        $('#rowsPerPage').on('change', function() {
            table.page.len(parseInt(this.value)).draw();
        });
        
        $(document).on('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                $('#customSearch').focus();
            }
        });
        
        $('.export-btn').on('click', function() {
            alert('Export functionality would be implemented here');
        });
        
        $('.share-btn').on('click', function() {
            alert('Share functionality would be implemented here');
        });
        
        $('.filter-btn').on('click', function() {
            alert('Filter functionality would be implemented here');
        });
        
        $('.edit-btn').on('click', function() {
            var row = $(this).closest('tr');
            var equipment = row.find('td:first').text();
            alert('Edit: ' + equipment);
        });
        
        $('.delete-btn').on('click', function() {
            var row = $(this).closest('tr');
            var equipment = row.find('td:first').text();
            if (confirm('Delete: ' + equipment + '?')) {
                table.row(row).remove().draw();
            }
        });
        
        $('.more-btn').on('click', function() {
            alert('More options menu would appear here');
        });
    });
    
    function goToPage() {
        var page = parseInt($('#pageInput').val()) - 1;
        var table = $('.equipmentTable').DataTable();
        var pageInfo = table.page.info();
        
        if (page >= 0 && page < pageInfo.pages) {
            table.page(page).draw('page');
        } else {
            alert('Invalid page number');
            $('#pageInput').val(pageInfo.page + 1);
        }
    }
    
    $('#pageInput').on('keypress', function(e) {
        if (e.which === 13) {
            goToPage();
        }
    });
</script>
@endsection

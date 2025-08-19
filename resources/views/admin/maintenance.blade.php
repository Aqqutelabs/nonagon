@extends('layouts.admin')

@section('nav_title','Maintenance')
@section('nav_desc','Schedule, track, and manage all maintenance activities')

@section('content')
<div class="quick-stats-section">
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
                                <ion-icon name="boat-outline" class="sc-img warning"></ion-icon>
                            </div>
                            <div class="sc-desc">
                                <h6>total tasks</h6>
                                <h2>7</h2>
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
                                <h6>overdue tasks</h6>
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
                                <ion-icon name="clipboard-outline" class="sc-img primary"></ion-icon>
                            </div>
                            <div class="sc-desc">
                                <h6>ongoing tasks</h6>
                                <h2>1</h2>
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
                                <h6>completed tasks</h6>
                                <h2>9</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mt-5">
    <div class="col-lg-5">
        <ul class="nav nav-tabs listings-section" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="kanban-tab" data-bs-toggle="tab" data-bs-target="#kanban" type="button" role="tab" aria-controls="kanban" aria-selected="true">
                    <ion-icon name="layers-outline"></ion-icon> Kanban View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tableview-tab" data-bs-toggle="tab" data-bs-target="#tableview" type="button" role="tab" aria-controls="tableview" aria-selected="false">
                    <ion-icon name="podium-outline"></ion-icon>
                    Table View
                </button>
            </li>
        </ul>
    </div>
    <div class="col-lg-7">
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
                    <button class="qd-button btn active" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        <ion-icon name="add-outline"></ion-icon>
                        add new task
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
<div class="qd-tabcontent tab-content">
    <div class="tab-pane fade show active" id="kanban" role="tabpanel" aria-labelledby="kanban-tab">

        <div class="maintenance-board mt-5">
            <div class="container-fluid">
                <div class="row">
                    <!-- Ongoing Tasks Column -->
                    <div class="col-lg-4 col-md-6 p-2">
                        <div class="status-column">
                            <div class="status-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="status-icon ongoing"></span>
                                        <h5 class="mb-0 fw-medium">Ongoing</h5>
                                        <span class="task-count p-2">3 open tasks</span>
                                    </div>
                                </div>
                            </div>
                            <div class="kanban-content">
                                <!-- Task Card 1 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge preventive">Preventive</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task Card 2 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge overdue">overdue</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task Card 3 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge upcoming">upcoming</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- upcoming Tasks Column -->
                    <div class="col-lg-4 col-md-6 p-2">
                        <div class="status-column">
                            <div class="status-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="status-icon upcoming"></span>
                                        <h5 class="mb-0 fw-medium">Upcoming</h5>
                                        <span class="task-count p-2">4 upcoming tasks</span>
                                    </div>
                                </div>
                            </div>
                            <div class="kanban-content">
                                <!-- Task Card 1 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge preventive">Preventive</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task Card 2 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge overdue">overdue</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task Card 3 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge upcoming">upcoming</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- completed Tasks Column -->
                    <div class="col-lg-4 col-md-6 p-2">
                        <div class="status-column">
                            <div class="status-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="status-icon completed"></span>
                                        <h5 class="mb-0 fw-medium">Completed</h5>
                                        <span class="task-count p-2">4 completed tasks</span>
                                    </div>
                                </div>
                            </div>
                            <div class="kanban-content">
                                <!-- Task Card 1 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge preventive">Preventive</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task Card 2 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge overdue">overdue</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task Card 3 -->
                                <div class="task-card">
                                    <div class="task-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="task-title">Quarter Oil Change</h6>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="task-meta assigned-name">
                                            <h5>Assigned to:</h5>
                                            <div class="assignee-avatar">OR</div>
                                            <span>Olivia Rhye</span>
                                        </div>
                                        
                                        <div class="task-meta-parent">
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Equipment ID: GEN-NX 24D</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-cog maintenance-type-icon"></i>
                                                <span>Maintenance type: Preventive</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Next Maintenance: 24/08/2025</span>
                                            </div>
                                            
                                            <div class="task-meta">
                                                <i class="fas fa-wrench"></i>
                                                <span>Due Date: 08/04/2025</span>
                                            </div>
                                        </div>
                                        
                                        <div class="task-actions">
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-cog me-1"></i> View Details
                                            </button>
                                            <button class="btn btn-task-action btn-view-details">
                                                <i class="fas fa-check me-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <span class="status-badge upcoming">upcoming</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-pane fade" id="tableview" role="tabpanel" aria-labelledby="tableview-tab">
        asuduyqwe
    </div>
</div>
@endsection
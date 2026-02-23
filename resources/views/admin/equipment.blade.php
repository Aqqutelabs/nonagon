@extends('layouts.admin')

@section('nav_title','Equipment')
@section('nav_desc','Monitor and manage all equipment across your facilities')
@section('backname','Equipment')

@section('local_css')
<link rel="stylesheet" href="{{ asset('assets/css/equipment-v2.css') }}">
@endsection

@section('content')
<div class="equipment-dashboard">
    <div class="summary-cards-row">
        <div class="summary-card">
            <div class="summary-card-header">
                <!-- icon -->
                <div class="summary-card-icon">
                    <ion-icon name="cube-outline"></ion-icon>
                </div>
                <!-- title and value -->
                <div class="summary-card-text">
                    <div class="summary-card-title">Total Assets</div>
                    <div class="summary-card-value">30</div>
                </div>
                <!-- analytics -->
                <div class="summary-card-trend trend-up">
                    <ion-icon name="trending-up-outline"></ion-icon> +3.2%
                </div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-card-header">
                <!-- icon -->
                <div class="summary-card-icon">
                    <ion-icon name="cube-outline"></ion-icon>
                </div>
                <!-- title and value -->
                <div class="summary-card-text">
                    <div class="summary-card-title">Active Assets</div>
                    <div class="summary-card-value">17</div>
                </div>
                <!-- analytics -->
                <div class="summary-card-trend trend-up">
                    <ion-icon name="trending-up-outline"></ion-icon> +5.1%
                </div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-card-header">
                <!-- icon -->
                <div class="summary-card-icon">
                    <ion-icon name="cube-outline"></ion-icon>
                </div>
                <!-- title and value -->
                <div class="summary-card-text">
                    <div class="summary-card-title">Under Maintenance</div>
                    <div class="summary-card-value">3</div>
                </div>
                <!-- analytics -->
                <div class="summary-card-trend trend-up">
                    <ion-icon name="trending-up-outline"></ion-icon> -2.4%
                </div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-card-header">
                <!-- icon -->
                <div class="summary-card-icon">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
                <!-- title and value -->
                <div class="summary-card-text">
                    <div class="summary-card-title">Total Operators</div>
                    <div class="summary-card-value">20</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="top-actions-row">
        <div class="search-wrapper">
            <input type="text" placeholder="Search equipment name, assest ID, brand, model, category or Operator...">
            <ion-icon name="search-outline"></ion-icon>
        </div>
        <div class="action-buttons">
            <button class="btn-primary-add" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                <ion-icon name="add-outline"></ion-icon>
                Add New Equipment
            </button>
            <button class="btn-export">
                <ion-icon name="download-outline" style="transform: rotate(-90deg);"></ion-icon>
            </button>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="left-content">
            <div class="filters-row">
                <div class="filters-left">
                    <select class="filter-dropdown">
                        <option>Status</option>
                        <option>Operational</option>
                        <option>Maintenance</option>
                    </select>
                    <select class="filter-dropdown">
                        <option>Category</option>
                        <option>Machinery</option>
                    </select>
                    <button class="btn-filter-toggle">Critical Only</button>
                </div>
                
                <div class="view-toggles">
                    <button class="view-toggle-btn active" id="btnGrid" onclick="toggleView('grid')"><ion-icon name="grid-outline"></ion-icon></button>
                    <button class="view-toggle-btn" id="btnList" onclick="toggleView('list')"><ion-icon name="list-outline"></ion-icon></button>
                </div>
            </div>
            
            <div class="equipment-grid" id="equipmentGrid">
                @php
                    $equipmentList = [
                        [
                            'category' => 'Machinery',
                            'title' => 'Hydraulic Press Model A',
                            'assetId' => 'EQ001',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2 hours ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Hydraulic Press Model A',
                            'assetId' => 'EQ002',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Maintenance',
                            'owner' => 'John Smith',
                            'time' => '2 hours ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Air Compressor 300L',
                            'assetId' => 'EQ003',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2 hours ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Hydraulic Press Model A',
                            'assetId' => 'EQ001',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2 hours ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Air Compressor 300L',
                            'assetId' => 'EQ003',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2 hours ago',
                            'image' => 'assets/img/truck.png'
                        ]
                    ];
                @endphp
                
                @foreach($equipmentList as $equipment)
                <div class="eq-card" onclick="toggleCard(this)">
                    <div class="eq-card-img-placeholder">
                        <img src="{{ asset($equipment['image']) }}" alt="{{ $equipment['title'] }}">
                    </div>
                    <div class="eq-card-content">
                        <div class="eq-card-header">
                            <div>
                                <h3 class="eq-card-title">{{ $equipment['title'] }}</h3>
                                <div class="eq-card-id">{{ $equipment['assetId'] }}</div>
                            </div>
                            <div class="status-badge {{ $equipment['status'] == 'Operational' ? 'status-operational' : 'status-maintenance' }}">
                                {{ $equipment['status'] }}
                            </div>
                        </div>
                        
                        <div class="eq-card-details">
                            <div><strong>Category:</strong> {{ $equipment['category'] }}</div>
                            <div><strong>Location:</strong> {{ $equipment['location'] }}</div>
                        </div>
                        
                        <div class="eq-card-footer">
                            <div class="eq-card-owner">{{ $equipment['owner'] }}</div>
                            <div class="eq-card-time">{{ $equipment['time'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="equipment-list" id="equipmentList">
                <table class="equipment-table">
                    <thead>
                        <tr>
                            <th>Name / Asset ID</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Operator</th>
                            <th>Last Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipmentList as $equipment)
                        <tr>
                            <td>
                                <div class="eq-table-title">{{ $equipment['title'] }}</div>
                                <div class="eq-table-id">{{ $equipment['assetId'] }}</div>
                            </td>
                            <td>{{ $equipment['category'] }}</td>
                            <td>{{ $equipment['location'] }}</td>
                            <td>
                                <div class="status-badge {{ $equipment['status'] == 'Operational' ? 'status-operational' : 'status-maintenance' }}">
                                    {{ $equipment['status'] }}
                                </div>
                            </td>
                            <td>{{ $equipment['owner'] }}</td>
                            <td>{{ $equipment['time'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-pagination">
                    <div class="table-info">Showing {{ count($equipmentList) }} of {{ count($equipmentList) }} status</div>
                    <div class="table-btns">
                        <button disabled>Previous</button>
                        <button>Next</button>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="right-sidebar">
            <div class="location-sidebar">
                <h3>Location</h3>
                <p>Filter by location</p>
                
                <button class="location-btn">All Locations</button>
                
                <div class="tree-view">
                    <div class="tree-node">
                        <div class="tree-node-item" onclick="toggleTreeNode(this)">
                            <ion-icon name="chevron-down-outline"></ion-icon> Block A
                        </div>
                        <div class="tree-children" style="display: block;">
                            <div class="tree-node">
                                <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                    <ion-icon name="chevron-down-outline"></ion-icon> Production Site
                                </div>
                                <div class="tree-children" style="display: block;">
                                    <div class="tree-node">
                                        <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                            <ion-icon name="chevron-down-outline"></ion-icon> Assembly Plant
                                        </div>
                                        <div class="tree-children" style="display: block;">
                                            <div class="tree-leaf">Unit 1 <span class="leaf-badge">12</span></div>
                                            <div class="tree-leaf">Unit 2 <span class="leaf-badge">10</span></div>
                                            <div class="tree-leaf">Unit 3 <span class="leaf-badge">4</span></div>
                                        </div>
                                    </div>
                                    <div class="tree-node">
                                        <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                            <ion-icon name="chevron-forward-outline"></ion-icon> Packaging Plant
                                        </div>
                                        <div class="tree-children">
                                            <div class="tree-leaf">Unit 1 <span class="leaf-badge">12</span></div>
                                            <div class="tree-leaf">Unit 2 <span class="leaf-badge">10</span></div>
                                        </div>
                                    </div>
                                    <div class="tree-node">
                                        <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                            <ion-icon name="chevron-forward-outline"></ion-icon> Warehouse
                                        </div>
                                        <div class="tree-children">
                                        </div>
                                    </div>
                                    <div class="tree-node">
                                        <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                            <ion-icon name="chevron-forward-outline"></ion-icon> Storage Plant
                                        </div>
                                        <div class="tree-children">
                                            <div class="tree-leaf">Unit 1 <span class="leaf-badge">4</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tree-node">
                        <div class="tree-node-item" onclick="toggleTreeNode(this)">
                            <ion-icon name="chevron-down-outline"></ion-icon> Block B
                        </div>
                        <div class="tree-children" style="display: block;">
                            <div class="tree-node">
                                <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                    <ion-icon name="chevron-forward-outline"></ion-icon> Distribution Center
                                </div>
                                <div class="tree-children">
                                </div>
                            </div>
                            <div class="tree-node">
                                <div class="tree-node-item" onclick="toggleTreeNode(this)">
                                    <ion-icon name="chevron-forward-outline"></ion-icon> Logistics Plant
                                </div>
                                <div class="tree-children">
                                    <div class="tree-leaf">Unit 1 <span class="leaf-badge">9</span></div>
                                    <div class="tree-leaf">Unit 2 <span class="leaf-badge">12</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
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
@endsection

@section('local_js')
<script>
    function toggleCard(element) {
        element.classList.toggle('expanded');
    }

    function toggleView(viewType) {
        const gridView = document.getElementById('equipmentGrid');
        const listView = document.getElementById('equipmentList');
        const btnGrid = document.getElementById('btnGrid');
        const btnList = document.getElementById('btnList');
        
        if (viewType === 'grid') {
            gridView.style.display = 'grid';
            listView.style.display = 'none';
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        } else {
            gridView.style.display = 'none';
            listView.style.display = 'block';
            btnGrid.classList.remove('active');
            btnList.classList.add('active');
        }
    }

    function toggleTreeNode(element) {
        const parentNode = element.closest('.tree-node');
        const children = parentNode.querySelector('.tree-children');
        const icon = element.querySelector('ion-icon');
        
        if (children) {
            const isVisible = children.style.display === 'block';
            if (isVisible) {
                children.style.display = 'none';
                icon.name = 'chevron-forward-outline';
            } else {
                children.style.display = 'block';
                icon.name = 'chevron-down-outline';
            }
        }
    }
</script>
@endsection

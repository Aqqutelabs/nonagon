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
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.39519 4.5H8.35352C5.40724 4.5 3.9341 4.5 3.01881 5.37868C2.10352 6.25736 2.10352 7.67157 2.10352 10.5V14.5C2.10352 17.3284 2.10352 18.7426 3.01881 19.6213C3.9341 20.5 5.40724 20.5 8.35352 20.5H12.5601C15.5064 20.5 16.9795 20.5 17.8948 19.6213C18.4878 19.052 18.6966 18.2579 18.7701 17" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16.1667 7V3.85355C16.1667 3.65829 16.3316 3.5 16.535 3.5C16.6326 3.5 16.7263 3.53725 16.7954 3.60355L21.5275 8.14645C21.7634 8.37282 21.8958 8.67986 21.8958 9C21.8958 9.32014 21.7634 9.62718 21.5275 9.85355L16.7954 14.3964C16.7263 14.4628 16.6326 14.5 16.535 14.5C16.3316 14.5 16.1667 14.3417 16.1667 14.1464V11H13.1157C8.875 11 7.3125 14.5 7.3125 14.5V12C7.3125 9.23858 9.64435 7 12.5208 7H16.1667Z" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="left-content">
            <div class="filters-row">
                <div class="filters-left">
                    <select class="filter-dropdown">
                        <option disabled selected>Status</option>
                        <option>All</option>
                        <option>Operational</option>
                        <option>Maintenance</option>
                        <option>Critical</option>
                    </select>
                    <select class="filter-dropdown">
                        <option disabled selected>Category</option>
                        <option>Machinery</option>
                        <option>Vehicles</option>
                        <option>Tools</option>
                        <option>Electronics</option>
                        <option>Safety Equipment</option>
                    </select>
                    <button class="btn-filter-toggle">Critical Only</button>
                </div>
                
                <div class="view-toggles">
                    <button class="view-toggle-btn active" id="btnGrid" onclick="toggleView('grid')">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.852 3.88892H14.2594C9.37076 3.88892 6.92645 3.88892 5.40774 5.40762C3.88904 6.92633 3.88904 9.37064 3.88904 14.2593V16.8519C3.88904 21.7405 3.88904 24.1849 5.40774 25.7035C6.92645 27.2222 9.37076 27.2223 14.2594 27.2223H16.852C21.7406 27.2223 24.185 27.2222 25.7036 25.7035C27.2224 24.1849 27.2224 21.7405 27.2224 16.8519V14.2593C27.2224 9.37064 27.2224 6.92633 25.7036 5.40762C24.185 3.88892 21.7406 3.88892 16.852 3.88892Z" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11.6669 3.88892V27.2223" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19.4446 3.88892V27.2223" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M27.2223 11.6667H3.88904" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M27.2223 19.4446H3.88904" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button class="view-toggle-btn" id="btnList" onclick="toggleView('list')">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.3705 6.48169H25.926" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round"/>
                            <path d="M5.1853 6.48169H5.19694" stroke="#141B34" stroke-width="2.59259" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.1853 15.5557H5.19694" stroke="#141B34" stroke-width="2.59259" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.1853 24.6296H5.19694" stroke="#141B34" stroke-width="2.59259" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.3705 15.5557H25.926" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round"/>
                            <path d="M10.3705 24.6296H25.926" stroke="#141B34" stroke-width="1.94444" stroke-linecap="round"/>
                        </svg>
                    </button>
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
                            'time' => '2h ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Hydraulic Press Model A',
                            'assetId' => 'EQ002',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Maintenance',
                            'owner' => 'John Smith',
                            'time' => '2h ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Air Compressor 300L',
                            'assetId' => 'EQ003',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2h ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Hydraulic Press Model A',
                            'assetId' => 'EQ001',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2h ago',
                            'image' => 'assets/img/truck.png'
                        ],
                        [
                            'category' => 'Machinery',
                            'title' => 'Air Compressor 300L',
                            'assetId' => 'EQ003',
                            'location' => 'Plan A - Unit 3',
                            'status' => 'Operational',
                            'owner' => 'John Smith',
                            'time' => '2h ago',
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content eq-modal-content">
            <div class="modal-header eq-modal-header border-bottom-0 pb-0">
                <h1 class="modal-title fs-5" id="staticBackdropLabel" style="font-weight: 700; color: #1a1d20;">Add Equipment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-4">
                <form action="#">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold" style="font-size: 13px; color: #525866;">Equipment Name</label>
                            <input type="text" class="form-control eq-form-control" name="eqp_name" placeholder="e.g CNC Lathe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px; color: #525866;">Asset ID</label>
                            <input type="text" class="form-control eq-form-control" name="equipment_id" placeholder="e.g AST-013">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold" style="font-size: 13px; color: #525866;">Category</label>
                            <select name="category" class="form-select eq-form-control">
                                <option value="" disabled selected>Select Category</option>
                                <option value="Machinery">Machinery</option>
                                <option value="Vehicles">Vehicles</option>
                                <option value="Tools">Tools</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px; color: #525866;">Status</label>
                            <select name="status" class="form-select eq-form-control">
                                <option value="Operational" selected>Operational</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="font-size: 13px; color: #525866;">Operator</label>
                            <select name="operator" class="form-select eq-form-control">
                                <option value="" disabled selected>Assign Operator</option>
                                <option value="John Smith">John Smith</option>
                                <option value="Mike Johnson">Mike Johnson</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-1 mb-2">
                        <button type="button" class="btn btn-outline-secondary eq-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary eq-btn-primary">Add New Equipment</button>
                    </div>
                </form>
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

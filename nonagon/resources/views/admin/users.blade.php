@extends('layouts.admin')

@section('nav_title','User Management')
@section('nav_desc','Monitor and manage all equipment across your facilities')
@section('backname','User Management')

@section('local_css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/equipment-table.css') }}">
@endsection
@section('content')



<div class="col-lg-12">
    <div class="quick-stats-section">
    <h4 class="body-title">key metrics</h4>
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
                                <h6>total staff</h6>
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
                                <ion-icon name="timer-outline" class="sc-img success"></ion-icon>
                            </div>
                            <div class="sc-desc">
                                <h6>active staff</h6>
                                <h2>12</h2>
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
                                <ion-icon name="settings-outline" class="sc-img primary"></ion-icon>
                            </div>
                            <div class="sc-desc">
                                <h6>technician</h6>
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
                                <ion-icon name="warning-outline" class="sc-img warning"></ion-icon>
                            </div>
                            <div class="sc-desc">
                                <h6>tasks overdue</h6>
                                <h2>4</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="equipment-container mt-3">
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
                
                <div>
                    <button class="qd-button btn active" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        <ion-icon name="add-outline"></ion-icon>
                        add new staff
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
                                <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Add New Staff</b></h1>
                                <h6>Add a new staff to your organization</h6>
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
                                        Staff Name
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="eqp_name" name="eqp_name" placeholder="Enter Name">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-12 mb-4">
                                <div class="row">
                                    <div class="col-lg-4">
                                        Role
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <select name="role" id="role" class="form-control">
                                                <option value="">Select Role</option>
                                                <option value="technician">Technician</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-12 mb-4">
                                <div class="row">
                                    <div class="col-lg-4">
                                        Email Contact
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="email_contact" name="email_contact" placeholder="Paste Profile URL here">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-12 mb-4">
                                <div class="row">
                                    <div class="col-lg-4">
                                        State/City
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="state_city" name="state_city" placeholder="State/City Of Operation">
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
                                <button class="btn qd-button active">
                                    Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- DataTable -->
        <table class="display equipmentTable">
            <thead>
                <tr>
                    <th colspan="1">staff</th>
                    <th colspan="1">role</th>
                    <th colspan="1">staff id</th>
                    <th colspan="1">last login</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td class="d-flex align-items-center">
                        <img src="{{ asset("assets/img/2.jpg") }}" alt="john smith" class="avatar">
                        <div class="user-info-parent">
                            <h6 class="named">john smith</h6>
                            <h6 class="small">johnsmith@gmail.com</h6>
                        </div>
                    </td>
                    <td><span class="status-badge active">Technician</span></td>
                    <td>0023DZGI</td>
                    <td>2025-06-17 16:24pm</td>
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
                    <td class="d-flex align-items-center">
                        <img src="{{ asset("assets/img/2.jpg") }}" alt="john smith" class="avatar">
                        <div class="user-info-parent">
                            <h6 class="named">john smith</h6>
                            <h6 class="small">johnsmith@gmail.com</h6>
                        </div>
                    </td>
                    <td><span class="status-badge preventive">admin</span></td>
                    <td>0023DZGI</td>
                    <td>2025-06-17 16:24pm</td>
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

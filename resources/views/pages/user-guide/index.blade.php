@extends('adminlte::page')

@section('title', 'User Guide')

@section('content_header')
    <div class="page-header-bar">
        <div class="page-header-left">
            <div class="page-header-info">
                <h1 class="page-header-title">User Guide</h1>
                <span class="page-header-sub">
                    <i class="fa fa-book-open mr-1"></i>
                    Step-by-step guide for using the system as an account user
                </span>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="row">

    {{-- Table of Contents --}}
    <div class="col-md-3 ug-toc-col">
        <div class="ug-toc card card-outline card-primary" style="position: fixed; top: 162px; z-index: 100;">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-list mr-1"></i> Contents</h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav flex-column ug-toc-nav">
                    <li class="nav-item"><a class="nav-link" href="#section-getting-started">Getting Started</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-account-branch">Account &amp; Branch</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-app-menu">App Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-sales">Sales</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-inventory">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-purchase-orders">Purchase Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-rtv">Return to Vendors</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-stock-on-hand">Stock on Hand</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-stock-transfers">Stock Transfers</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-reports">Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-customers">Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-maintenance">Maintenance</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-profile">Profile</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Guide Body --}}
    <div class="col-md-9 pl-4">

        {{-- Getting Started --}}
        <div id="section-getting-started" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-sign-in-alt mr-2"></i>Getting Started</h3>
            </div>
            <div class="card-body">
                <p>This system is a Sales-to-Outlet (STO) management portal used to upload and monitor sales data, inventory, purchase orders, and related business operations across multiple accounts and branches.</p>

                <h6 class="ug-subsection-title">Logging In</h6>
                <ol class="ug-steps">
                    <li>Navigate to the system login page.</li>
                    <li>Enter your <strong>username</strong> and <strong>password</strong>, then click <strong>Login</strong>.</li>
                    <li>Alternatively, click <strong>Sign in with Google</strong> if your organization uses Google authentication.</li>
                </ol>

                <div class="alert alert-info ug-tip">
                    <i class="fa fa-info-circle mr-1"></i>
                    <strong>Tip:</strong> If you cannot log in, contact your system administrator to verify your account credentials and assigned permissions.
                </div>

                <h6 class="ug-subsection-title mt-3">Logging Out</h6>
                <p>Click your name in the top navigation bar, then select <strong>Sign Out</strong> (or <strong>Logout</strong>) from the dropdown menu.</p>
            </div>
        </div>

        {{-- Account & Branch Selection --}}
        <div id="section-account-branch" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-building mr-2"></i>Account &amp; Branch Selection</h3>
            </div>
            <div class="card-body">
                <p>After logging in, you must select an account and a branch before accessing any module. The system is multi-tenant — each account has its own set of branches and data.</p>

                <h6 class="ug-subsection-title">Step 1 — Select an Account</h6>
                <ol class="ug-steps">
                    <li>You will see a grid of account tiles. Each tile shows the <strong>account code</strong> and <strong>name</strong>.</li>
                    <li>Use the <strong>search bar</strong> to filter accounts by code or name.</li>
                    <li>Click the account tile you want to work with.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Step 2 — Select a Branch</h6>
                <ol class="ug-steps">
                    <li>After selecting an account, a list of branches assigned to that account is displayed.</li>
                    <li>Use the <strong>search bar</strong> to filter branches.</li>
                    <li>Click the branch tile to enter the branch workspace.</li>
                </ol>

                <div class="alert alert-warning ug-tip">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    <strong>Note:</strong> All data you view and upload is scoped to the selected account and branch. Use the <strong>Change Branch</strong> button in the header bar to switch branches at any time.
                </div>
            </div>
        </div>

        {{-- App Menu --}}
        <div id="section-app-menu" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-th-large mr-2"></i>App Menu</h3>
            </div>
            <div class="card-body">
                <p>Once inside a branch, the <strong>App Menu</strong> is your central navigation hub. It shows all modules available to you based on your role and permissions.</p>

                <h6 class="ug-subsection-title">Module Groups</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr><th>Group</th><th>Modules</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Reports</strong></td>
                            <td>STO Report, VMI Report</td>
                        </tr>
                        <tr>
                            <td><strong>Uploads</strong></td>
                            <td>Sales, Inventory, Purchase Orders, RTV, Stock on Hand, Stock Transfers</td>
                        </tr>
                        <tr>
                            <td><strong>Maintenance</strong></td>
                            <td>Location, Area, District, Salesman, Customer, Channel</td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-info ug-tip">
                    <i class="fa fa-info-circle mr-1"></i>
                    Only the modules you have permission to access will appear. If a module is missing, contact your administrator to request the appropriate role/permission.
                </div>
            </div>
        </div>

        {{-- Sales --}}
        <div id="section-sales" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-money-check-alt mr-2"></i>Sales</h3>
            </div>
            <div class="card-body">
                <p>The Sales module is used to upload and manage sales transaction data for the branch. Each upload creates a batch of sales line items tied to customers, products, salesmen, and locations.</p>

                <h6 class="ug-subsection-title">Uploading Sales Data</h6>
                <ol class="ug-steps">
                    <li>From the App Menu or sidebar, go to <strong>Sales</strong>.</li>
                    <li>Click the <strong>Upload</strong> button (requires <em>sales upload</em> permission).</li>
                    <li>Select your sales file and submit. A progress indicator will track the upload in real time.</li>
                    <li>Once complete, the upload batch appears in the list showing the <strong>date, user, SKU count, total amount,</strong> and <strong>credit memo amount</strong>.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Viewing a Sales Batch</h6>
                <p>Click the <i class="fa fa-info-circle text-info"></i> <strong>View</strong> icon on any upload row to see the individual sales line items within that batch.</p>

                <h6 class="ug-subsection-title mt-3">Available Actions</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Action</th><th>Icon</th><th>Permission Required</th></tr></thead>
                    <tbody>
                        <tr><td>View line items</td><td><i class="fa fa-info-circle text-info"></i></td><td>sales access</td></tr>
                        <tr><td>Export / Download</td><td><i class="fa fa-download text-secondary"></i></td><td>sales access</td></tr>
                        <tr><td>Edit batch</td><td><i class="fa fa-pen text-warning"></i></td><td>sales edit</td></tr>
                        <tr><td>Delete batch</td><td><i class="fa fa-trash text-danger"></i></td><td>sales delete</td></tr>
                        <tr><td>Restore deleted batch</td><td><i class="fa fa-recycle text-success"></i></td><td>sales restore</td></tr>
                    </tbody>
                </table>

                <div class="alert alert-warning ug-tip">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    <strong>Credit Memos:</strong> Invoice numbers prefixed with <code>PSC-</code> are treated as credit memos and are tracked separately in the <em>CM Amount</em> column.
                </div>
            </div>
        </div>

        {{-- Inventory --}}
        <div id="section-inventory" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-warehouse mr-2"></i>Inventory</h3>
            </div>
            <div class="card-body">
                <p>The Inventory module lets you upload and track product inventory records per warehouse location and date. Each upload batch groups inventory by location and date.</p>

                <h6 class="ug-subsection-title">Uploading Inventory</h6>
                <ol class="ug-steps">
                    <li>Go to <strong>Inventory</strong> from the App Menu or sidebar.</li>
                    <li>Click the <strong>Upload</strong> button (requires <em>inventory upload</em> permission).</li>
                    <li>Select your inventory file and submit. Upload progress is tracked in real time.</li>
                    <li>The upload batch appears in the list with the date, user, and total inventory count.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Available Actions</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Action</th><th>Icon</th><th>Permission Required</th></tr></thead>
                    <tbody>
                        <tr><td>View inventory details</td><td><i class="fa fa-info-circle text-info"></i></td><td>inventory access</td></tr>
                        <tr><td>Edit batch</td><td><i class="fa fa-pen text-warning"></i></td><td>inventory edit</td></tr>
                        <tr><td>Export / Download</td><td><i class="fa fa-download text-secondary"></i></td><td>inventory export</td></tr>
                        <tr><td>Delete batch</td><td><i class="fa fa-trash text-danger"></i></td><td>inventory delete</td></tr>
                        <tr><td>Restore deleted batch</td><td><i class="fa fa-recycle text-success"></i></td><td>inventory restore</td></tr>
                    </tbody>
                </table>

                <div class="alert alert-info ug-tip">
                    <i class="fa fa-info-circle mr-1"></i>
                    SKU codes prefixed with <code>FG-</code> are Free Goods and <code>PRM-</code> are Promo items. Unprefixed codes are treated as Normal inventory.
                </div>
            </div>
        </div>

        {{-- Purchase Orders --}}
        <div id="section-purchase-orders" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-shopping-cart mr-2"></i>Purchase Orders</h3>
            </div>
            <div class="card-body">
                <p>The Purchase Orders module lets you create, upload, and track purchase orders for the branch. Each PO includes order details, shipping information, and line-item totals.</p>

                <h6 class="ug-subsection-title">Creating a Purchase Order</h6>
                <ol class="ug-steps">
                    <li>Go to <strong>Purchase Orders</strong> from the App Menu.</li>
                    <li>Click the <strong>Create</strong> button (requires <em>purchase order create</em> permission).</li>
                    <li>Fill in the required fields: order date, ship date, shipping instructions, and ship-to details.</li>
                    <li>Add line items, then save the PO.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Uploading Purchase Orders in Bulk</h6>
                <ol class="ug-steps">
                    <li>Click the <strong>Upload</strong> button (requires <em>purchase order upload</em> permission).</li>
                    <li>Select your PO file and submit.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Purchase Order List</h6>
                <p>The list shows each PO with its <strong>PO number, status, order/ship dates, ship-to details, total quantity, gross amount,</strong> and <strong>net amount</strong>. A totals row at the bottom summarizes all visible records. Click any PO number to view full details.</p>

                <h6 class="ug-subsection-title mt-3">PO Statuses</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Status</th><th>Meaning</th></tr></thead>
                    <tbody>
                        <tr><td><span class="badge badge-secondary">Draft</span></td><td>PO is saved but not yet submitted. Can still be edited.</td></tr>
                        <tr><td><span class="badge badge-primary">Submitted</span></td><td>PO has been submitted for processing.</td></tr>
                        <tr><td><span class="badge badge-success">Approved</span></td><td>PO has been approved and is being fulfilled.</td></tr>
                    </tbody>
                </table>

                <div class="alert alert-warning ug-tip">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    Only <strong>Draft</strong> POs can be edited. Once a PO is submitted or approved, the edit action is disabled.
                </div>
            </div>
        </div>

        {{-- Return to Vendors --}}
        <div id="section-rtv" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-undo mr-2"></i>Return to Vendors (RTV)</h3>
            </div>
            <div class="card-body">
                <p>The Return to Vendors module tracks product returns sent back to suppliers. Each RTV record contains the document reference, return reason, ship date, and destination details.</p>

                <h6 class="ug-subsection-title">Creating an RTV</h6>
                <ol class="ug-steps">
                    <li>Go to <strong>RTV</strong> from the App Menu.</li>
                    <li>Click the <strong>Add RTV</strong> button (requires <em>rtv create</em> permission).</li>
                    <li>Fill in the document number, ship date, reason, and ship-to details, then save.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Uploading RTVs in Bulk</h6>
                <ol class="ug-steps">
                    <li>Click the <strong>Upload RTV</strong> button (requires <em>rtv upload</em> permission).</li>
                    <li>Select your RTV file and submit.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Viewing RTV Details</h6>
                <p>Click the <i class="fa fa-list text-info"></i> icon on any RTV row to view its full details including all line items.</p>
            </div>
        </div>

        {{-- Stock on Hand --}}
        <div id="section-stock-on-hand" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-boxes mr-2"></i>Stock on Hand</h3>
            </div>
            <div class="card-body">
                <p>The Stock on Hand module captures current stock levels per customer/store location, organized by year and month.</p>

                <h6 class="ug-subsection-title">Uploading Stock on Hand Data</h6>
                <ol class="ug-steps">
                    <li>Go to <strong>Stock on Hand</strong> from the App Menu.</li>
                    <li>Click the <strong>Upload</strong> button (requires <em>stock on hand upload</em> permission).</li>
                    <li>Select your file and submit.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Reading the List</h6>
                <p>Each row in the list shows the <strong>customer code, customer name, year, month, upload date,</strong> and <strong>total inventory units</strong> on hand for that period.</p>
            </div>
        </div>

        {{-- Stock Transfers --}}
        <div id="section-stock-transfers" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-exchange-alt mr-2"></i>Stock Transfers</h3>
            </div>
            <div class="card-body">
                <p>The Stock Transfers module tracks product transfers between locations and provides a year-over-year (YoY) comparison for each transfer record.</p>

                <h6 class="ug-subsection-title">Uploading Stock Transfer Data</h6>
                <ol class="ug-steps">
                    <li>Go to <strong>Stock Transfers</strong> from the App Menu.</li>
                    <li>Click the <strong>Upload</strong> button (requires <em>stock transfer upload</em> permission).</li>
                    <li>Select your file and submit.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Reading the Transfer List</h6>
                <p>Each summary row shows:</p>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Column</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td>Customer Code / Name</td><td>The store or location the transfer belongs to</td></tr>
                        <tr><td>Year / Month</td><td>Reporting period</td></tr>
                        <tr><td>Total Transfer TY</td><td>This year's transfer quantity</td></tr>
                        <tr><td>Total Transfer LY</td><td>Last year's transfer quantity (same period)</td></tr>
                        <tr><td>Growth %</td><td>YoY growth percentage. Shown in <span class="text-danger font-weight-bold">red</span> if negative.</td></tr>
                    </tbody>
                </table>
                <p>Click any row to expand it and see <strong>product-level</strong> breakdown with individual SKU growth figures.</p>
            </div>
        </div>

        {{-- Reports --}}
        <div id="section-reports" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-chart-line mr-2"></i>Reports</h3>
            </div>
            <div class="card-body">
                <p>The Reports module provides analytical views of sales and inventory data with interactive charts and filters.</p>

                <h6 class="ug-subsection-title">Available Reports</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Report</th><th>Description</th><th>Permission</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><strong>STO Report</strong></td>
                            <td>Sales-to-Outlet report showing sales performance by period, salesman, or channel.</td>
                            <td>report sto</td>
                        </tr>
                        <tr>
                            <td><strong>VMI Report</strong></td>
                            <td>Vendor Managed Inventory report for tracking stock against sales targets.</td>
                            <td>report vmi</td>
                        </tr>
                    </tbody>
                </table>

                <h6 class="ug-subsection-title mt-3">Using Filters</h6>
                <ol class="ug-steps">
                    <li>Select the desired report from the Reports section of the App Menu.</li>
                    <li>Use the filter controls (year, month, salesman, channel, etc.) to narrow down the data.</li>
                    <li>The charts and tables update automatically to reflect your selection.</li>
                </ol>
            </div>
        </div>

        {{-- Customers --}}
        <div id="section-customers" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-users mr-2"></i>Customers</h3>
            </div>
            <div class="card-body">
                <p>The Customers module manages the master list of stores/outlets (customers) assigned to the branch. Customer records link to a salesman and a channel.</p>

                <h6 class="ug-subsection-title">Adding a Customer</h6>
                <ol class="ug-steps">
                    <li>Go to <strong>Customers</strong> from the App Menu.</li>
                    <li>Click <strong>Add Customer</strong> (requires <em>customer create</em> permission).</li>
                    <li>Fill in the customer code, name, address fields, channel, and assigned salesman, then save.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Uploading Customers in Bulk</h6>
                <ol class="ug-steps">
                    <li>Click the <strong>Upload</strong> button in the customer list (requires <em>customer upload</em> permission).</li>
                    <li>Select your customer file and submit.</li>
                </ol>

                <h6 class="ug-subsection-title mt-3">Available Actions</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Action</th><th>Icon</th><th>Permission</th></tr></thead>
                    <tbody>
                        <tr><td>View details</td><td><i class="fa fa-info-circle text-info"></i></td><td>customer access</td></tr>
                        <tr><td>Edit customer</td><td><i class="fa fa-pen text-warning"></i></td><td>customer edit</td></tr>
                        <tr><td>Delete customer</td><td><i class="fa fa-trash text-danger"></i></td><td>customer delete</td></tr>
                        <tr><td>Restore deleted</td><td><i class="fa fa-recycle text-success"></i></td><td>customer restore</td></tr>
                    </tbody>
                </table>

                <h6 class="ug-subsection-title mt-3">Parked Customers</h6>
                <p>Customers flagged with unusual activity are moved to the <strong>Parked</strong> list. Use the <strong>Parked</strong> button (requires <em>customer parked</em> permission) to review, validate, and resolve these records.</p>
            </div>
        </div>

        {{-- Maintenance --}}
        <div id="section-maintenance" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-tools mr-2"></i>Maintenance</h3>
            </div>
            <div class="card-body">
                <p>Maintenance modules manage the master data used across the system. All records support create, view, edit, soft-delete, and restore operations.</p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="ug-module-card">
                            <h6><i class="fa fa-warehouse mr-1 text-primary"></i> Locations (Warehouses)</h6>
                            <p>Define warehouse or delivery locations used when uploading sales and inventory. Each location has a unique <strong>code</strong> and <strong>name</strong>.</p>
                            <p class="text-muted small">Permission prefix: <code>location</code></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ug-module-card">
                            <h6><i class="fa fa-map mr-1 text-primary"></i> Areas</h6>
                            <p>Geographic areas used to group customers and assign salesmen. Each area has a unique <strong>code</strong> and <strong>name</strong>.</p>
                            <p class="text-muted small">Permission prefix: <code>area</code></p>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <div class="ug-module-card">
                            <h6><i class="fa fa-map-marked-alt mr-1 text-primary"></i> Districts</h6>
                            <p>Districts group multiple areas together. A district is identified by a <strong>district code</strong> and has one or more assigned areas.</p>
                            <p class="text-muted small">Permission prefix: <code>district</code></p>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <div class="ug-module-card">
                            <h6><i class="fa fa-user-tie mr-1 text-primary"></i> Salesmen</h6>
                            <p>Manage sales representative records. Each salesman has a <strong>code, name, type,</strong> and is assigned to a <strong>district</strong>.</p>
                            <p class="text-muted small">Permission prefix: <code>salesman</code></p>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <div class="ug-module-card">
                            <h6><i class="fa fa-layer-group mr-1 text-primary"></i> Channels</h6>
                            <p>Customer channel or trade segment types (e.g., Grocery, Convenience). Each channel has a unique <strong>code</strong> and <strong>name</strong>.</p>
                            <p class="text-muted small">Permission prefix: <code>channel</code></p>
                        </div>
                    </div>
                </div>

                <h6 class="ug-subsection-title mt-4">Common Operations</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Action</th><th>How to perform</th></tr></thead>
                    <tbody>
                        <tr><td>Create a record</td><td>Click the <strong>Add / Create</strong> button, fill in the form, and save.</td></tr>
                        <tr><td>View a record</td><td>Click the <i class="fa fa-info-circle text-info"></i> icon or the record's name/code link.</td></tr>
                        <tr><td>Edit a record</td><td>Click the <i class="fa fa-pen text-warning"></i> icon, update the fields, and save.</td></tr>
                        <tr><td>Delete a record</td><td>Click the <i class="fa fa-trash text-danger"></i> icon. The record is soft-deleted and can be restored.</td></tr>
                        <tr><td>Restore a deleted record</td><td>Click the <i class="fa fa-recycle text-success"></i> icon on the deleted row.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Profile --}}
        <div id="section-profile" class="ug-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-user-circle mr-2"></i>Profile</h3>
            </div>
            <div class="card-body">
                <p>Your profile page shows your account details including your <strong>name, email, username,</strong> and assigned <strong>role</strong>.</p>

                <h6 class="ug-subsection-title">Accessing Your Profile</h6>
                <ol class="ug-steps">
                    <li>Click your name in the top navigation bar.</li>
                    <li>Select <strong>Profile</strong> from the dropdown menu.</li>
                </ol>

                <div class="alert alert-info ug-tip">
                    <i class="fa fa-info-circle mr-1"></i>
                    To change your password or update your role permissions, contact your system administrator.
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .ug-section {
            margin-bottom: 1.5rem;
            scroll-margin-top: 70px;
        }
        .ug-toc-nav .nav-link {
            padding: 0.4rem 1rem;
            color: #495057;
            border-left: 3px solid transparent;
            font-size: 0.875rem;
        }
        .ug-toc-nav .nav-link:hover {
            color: #007bff;
            border-left-color: #007bff;
            background: #f8f9fa;
        }
        .ug-toc-nav .nav-link.active {
            color: #007bff;
            border-left-color: #007bff;
            font-weight: 600;
        }
        .ug-subsection-title {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        .ug-steps {
            padding-left: 1.4rem;
            margin-bottom: 0;
        }
        .ug-steps li {
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        .ug-tip {
            font-size: 0.875rem;
            padding: 0.6rem 1rem;
            margin-top: 0.75rem;
            margin-bottom: 0;
        }
        .ug-module-card {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 0.85rem 1rem;
            height: 100%;
        }
        .ug-module-card h6 {
            font-weight: 700;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }
        .ug-module-card p {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .table-sm td, .table-sm th { font-size: 0.85rem; }
        @media (max-width: 767.98px) {
            .ug-toc {
                position: relative !important;
                top: auto !important;
                width: auto !important;
                z-index: auto !important;
                margin-bottom: 1rem;
            }
            .col-md-9.pl-4 {
                padding-left: 15px !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tocCard = document.querySelector('.ug-toc');
            const tocCol  = document.querySelector('.ug-toc-col');

            function syncTocWidth() {
                if (window.innerWidth >= 768) {
                    tocCard.style.position = 'fixed';
                    tocCard.style.width = tocCol.offsetWidth + 'px';
                } else {
                    tocCard.style.position = '';
                    tocCard.style.width = '';
                }
            }
            syncTocWidth();
            window.addEventListener('resize', function () { syncTocWidth(); updateTocTop(); });

            const contentHeader = document.querySelector('.content-header');
            function updateTocTop() {
                if (window.innerWidth < 768) { return; }
                const headerBottom = contentHeader ? contentHeader.getBoundingClientRect().bottom : 0;
                tocCard.style.top = headerBottom <= 57 ? '80px' : '162px';
            }
            updateTocTop();
            window.addEventListener('scroll', updateTocTop);

            const sections = document.querySelectorAll('.ug-section');
            const links    = document.querySelectorAll('.ug-toc-nav .nav-link');

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        links.forEach(l => l.classList.remove('active'));
                        const active = document.querySelector(`.ug-toc-nav a[href="#${entry.target.id}"]`);
                        if (active) { active.classList.add('active'); }
                    }
                });
            }, { rootMargin: '-60px 0px -70% 0px' });

            sections.forEach(s => observer.observe(s));
        });
    </script>
@stop

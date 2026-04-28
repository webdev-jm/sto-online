@extends('adminlte::page')

@section('title', 'API Documentation')

@section('content_header')
    <div class="page-header-bar">
        <div class="page-header-left">
            <div class="page-header-info">
                <h1 class="page-header-title">API Documentation</h1>
                <span class="page-header-sub">
                    <i class="fa fa-code mr-1"></i>
                    REST API reference for external integrations
                </span>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="row">

    {{-- Table of Contents --}}
    <div class="col-md-3 api-toc-col">
        <div class="api-toc card card-outline card-primary" style="position: fixed; top: 162px; z-index: 100;">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-list mr-1"></i> Contents</h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav flex-column api-toc-nav">
                    <li class="nav-item"><a class="nav-link" href="#section-overview">Overview</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-auth">Authentication</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-branches">Branches</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-areas">Areas</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-channels">Channels</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-districts">Districts</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-locations">Locations</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-salesmen">Salesmen</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-customers">Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-inventory">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link" href="#section-sales">Sales</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Documentation Body --}}
    <div class="col-md-9 pl-4">

        {{-- Overview --}}
        <div id="section-overview" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-info-circle mr-2"></i>Overview</h3>
            </div>
            <div class="card-body">
                <p>This REST API allows external applications to read and write data for sales, inventory, customers, and related resources. All endpoints return JSON.</p>

                <h6 class="api-subsection-title">Base URL</h6>
                <code class="api-base-url">{{ url('api') }}</code>

                <h6 class="api-subsection-title mt-3">Authentication Methods</h6>
                <p>Depending on the endpoint, one or both of the following headers are required:</p>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr><th>Header</th><th>Value</th><th>Required for</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>Authorization</code></td>
                            <td><code>Bearer &lt;token&gt;</code></td>
                            <td>All endpoints except <code>POST /api/login</code></td>
                        </tr>
                        <tr>
                            <td><code>BRANCH-KEY</code></td>
                            <td>Your branch key string</td>
                            <td>All endpoints except Auth &amp; Branches list</td>
                        </tr>
                    </tbody>
                </table>

                <h6 class="api-subsection-title mt-3">Standard Response Format</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="small text-muted">Success</p>
                        <pre class="api-code"><code>{
    "success": true,
    "data": { ... }
}</code></pre>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-muted">Validation Error (422)</p>
                        <pre class="api-code"><code>{
    "success": false,
    "message": "Validation Error",
    "error": { ... }
}</code></pre>
                    </div>
                </div>
                <p class="small text-muted mt-2">Collection endpoints (list) wrap results in <code>"data": [...]</code> with <code>"links"</code> and <code>"meta"</code> pagination keys when using the <code>page</code> query parameter.</p>
            </div>
        </div>

        {{-- Authentication --}}
        <div id="section-auth" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-key mr-2"></i>Authentication</h3>
            </div>
            <div class="card-body">

                {{-- Login --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/login</span>
                        <span class="api-desc">Obtain a Bearer token</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">No authentication headers required.</p>
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>username</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>User's username</td></tr>
                                <tr><td><code>password</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>User's password</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "username": "admin",
    "password": "secret"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "user": {
        "name": "Admin User",
        "email": "admin@example.com",
        "username": "admin"
    },
    "token": "1|abc123xyz..."
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logout --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/logout</span>
                        <span class="api-desc">Revoke all tokens for the authenticated user</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">Requires <code>Authorization: Bearer &lt;token&gt;</code>. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "message": "Logged out successfully"
}</code></pre>
                    </div>
                </div>

            </div>
        </div>

        {{-- Branches --}}
        <div id="section-branches" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-sitemap mr-2"></i>Branches</h3>
            </div>
            <div class="card-body">

                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/branches</span>
                        <span class="api-desc">List branches assigned to the authenticated user</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">Requires <code>Authorization: Bearer &lt;token&gt;</code>. No request body. Returns paginated (10 per page).</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "code": "BR001",
            "name": "Main Branch",
            "branch_token": "branch-key-xyz...",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": null },
    "meta": { "current_page": 1, "per_page": 10, "total": 1 }
}</code></pre>
                    </div>
                </div>

                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/branch/generateKey</span>
                        <span class="api-desc">Regenerate the BRANCH-KEY for the current branch</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">Requires <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> header. No request body. Invalidates the previous key.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "BR001",
        "name": "Main Branch",
        "branch_token": "new-branch-key-abc...",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                    </div>
                </div>

            </div>
        </div>

        {{-- Areas --}}
        <div id="section-areas" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-map mr-2"></i>Areas</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All area endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/area</span>
                        <span class="api-desc">List all areas for the account</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>page</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Page number. If omitted, all records are returned.</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "code": "AREA01",
            "name": "Area North",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ]
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/area/create</span>
                        <span class="api-desc">Create a new area</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique area code within the branch</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Area name</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "AREA01",
    "name": "Area North"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "AREA01",
        "name": "Area North",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/area/{id}/get</span>
                        <span class="api-desc">Retrieve a single area by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the area's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "AREA01",
        "name": "Area North",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/area/{id}/update</span>
                        <span class="api-desc">Update an existing area</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique area code (excluding current record)</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Area name</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "AREA01",
    "name": "Area North Updated"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "AREA01",
        "name": "Area North Updated",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Channels --}}
        <div id="section-channels" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-layer-group mr-2"></i>Channels</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All channel endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/channel</span>
                        <span class="api-desc">List all channels (global, not branch-scoped)</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>page</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Page number. If omitted, all records are returned.</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "code": "GROCERY",
            "name": "Grocery",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ]
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/channel/create</span>
                        <span class="api-desc">Create a new channel</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Globally unique channel code</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Channel name</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "GROCERY",
    "name": "Grocery"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "GROCERY",
        "name": "Grocery",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/channel/{id}/get</span>
                        <span class="api-desc">Retrieve a single channel by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the channel's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "GROCERY",
        "name": "Grocery",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/channel/{id}/update</span>
                        <span class="api-desc">Update an existing channel</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Globally unique channel code (excluding current record)</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Channel name</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "GROCERY",
    "name": "Grocery Updated"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "GROCERY",
        "name": "Grocery Updated",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Districts --}}
        <div id="section-districts" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-map-marked-alt mr-2"></i>Districts</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All district endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/districts</span>
                        <span class="api-desc">List all districts for the account</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>page</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Page number. If omitted, all records are returned.</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "district_code": "DIST01",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z",
            "areas": [
                { "id": 1, "code": "AREA01", "name": "Area North", "created_at": "...", "updated_at": "..." }
            ]
        }
    ]
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/district/create</span>
                        <span class="api-desc">Create a new district</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>district_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique district code within the branch</td></tr>
                                <tr><td><code>area_codes</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Comma-separated area codes to assign (e.g. <code>AREA01,AREA02</code>)</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "district_code": "DIST01",
    "area_codes": "AREA01,AREA02"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "district_code": "DIST01",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z",
        "areas": [
            { "id": 1, "code": "AREA01", "name": "Area North", ... },
            { "id": 2, "code": "AREA02", "name": "Area South", ... }
        ]
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/district/{id}/get</span>
                        <span class="api-desc">Retrieve a single district by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the district's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "district_code": "DIST01",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z",
        "areas": [
            { "id": 1, "code": "AREA01", "name": "Area North", ... }
        ]
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/district/{id}/update</span>
                        <span class="api-desc">Update an existing district</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>district_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique district code (excluding current record)</td></tr>
                                <tr><td><code>area_codes</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Comma-separated area codes to assign (replaces existing)</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "district_code": "DIST01",
    "area_codes": "AREA01,AREA02,AREA03"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "district_code": "DIST01",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z",
        "areas": [
            { "id": 1, "code": "AREA01", "name": "Area North", ... },
            { "id": 2, "code": "AREA02", "name": "Area South", ... },
            { "id": 3, "code": "AREA03", "name": "Area East", ... }
        ]
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Locations --}}
        <div id="section-locations" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-warehouse mr-2"></i>Locations (Warehouses)</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All location endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/location</span>
                        <span class="api-desc">List all locations for the account</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>page</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Page number. If omitted, all records are returned.</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "code": "WH001",
            "name": "Main Warehouse",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ]
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/location/create</span>
                        <span class="api-desc">Create a new location</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique warehouse/location code within the branch</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Location name</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "WH001",
    "name": "Main Warehouse"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "WH001",
        "name": "Main Warehouse",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/location/{id}/get</span>
                        <span class="api-desc">Retrieve a single location by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the location's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "WH001",
        "name": "Main Warehouse",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/location/{id}/update</span>
                        <span class="api-desc">Update an existing location</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique code (excluding current record)</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Location name</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "WH001",
    "name": "Main Warehouse Updated"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "WH001",
        "name": "Main Warehouse Updated",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Salesmen --}}
        <div id="section-salesmen" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-user-tie mr-2"></i>Salesmen</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All salesman endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/salesman</span>
                        <span class="api-desc">List all salesmen for the account</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>page</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Page number. If omitted, all records are returned.</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "code": "SM001",
            "name": "Juan Dela Cruz",
            "type": "regular",
            "district": {
                "id": 1,
                "district_code": "DIST01",
                "created_at": "...",
                "updated_at": "...",
                "areas": [{ "id": 1, "code": "AREA01", "name": "Area North", ... }]
            },
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ]
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/salesman/create</span>
                        <span class="api-desc">Create a new salesman</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique salesman code within the branch</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Salesman full name</td></tr>
                                <tr><td><code>type</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Salesman type identifier</td></tr>
                                <tr><td><code>district_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>District code the salesman belongs to (must exist in branch)</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "SM001",
    "name": "Juan Dela Cruz",
    "type": "regular",
    "district_code": "DIST01"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "SM001",
        "name": "Juan Dela Cruz",
        "type": "regular",
        "district": {
            "id": 1,
            "district_code": "DIST01",
            "areas": [...]
        },
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/salesman/{id}/get</span>
                        <span class="api-desc">Retrieve a single salesman by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the salesman's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "SM001",
        "name": "Juan Dela Cruz",
        "type": "regular",
        "district": { "id": 1, "district_code": "DIST01", "areas": [...] },
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z"
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/salesman/{id}/update</span>
                        <span class="api-desc">Update an existing salesman</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique code (excluding current record)</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Salesman full name</td></tr>
                                <tr><td><code>type</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Salesman type identifier</td></tr>
                                <tr><td><code>district_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>District code (must exist in branch)</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "SM001",
    "name": "Juan Dela Cruz",
    "type": "senior",
    "district_code": "DIST01"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "SM001",
        "name": "Juan Dela Cruz",
        "type": "senior",
        "district": { "id": 1, "district_code": "DIST01", ... },
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z"
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Customers --}}
        <div id="section-customers" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-users mr-2"></i>Customers</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All customer endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/customer</span>
                        <span class="api-desc">List all customers for the account (ordered by newest first)</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>page</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Page number. If omitted, all records are returned.</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "code": "CUST001",
            "name": "Sample Store",
            "address": "123 Main St, Poblacion, Makati, Metro Manila",
            "street": "Main St",
            "brgy": "Poblacion",
            "city": "Makati",
            "province": "Metro Manila",
            "status": 1,
            "created_at": "2025-04-28T08:00:00.000000Z",
            "updated_at": "2025-04-28T08:00:00.000000Z",
            "salesman": { "id": 1, "code": "SM001", "name": "Juan Dela Cruz", ... },
            "channel": { "id": 1, "code": "GROCERY", "name": "Grocery", ... }
        }
    ]
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/customer/create</span>
                        <span class="api-desc">Create a new customer</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unique customer code within the branch</td></tr>
                                <tr><td><code>name</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Customer name</td></tr>
                                <tr><td><code>channel_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Channel code (must exist; channel mapping applied)</td></tr>
                                <tr><td><code>salesman_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Salesman code (must exist in branch)</td></tr>
                                <tr><td><code>address</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Full address</td></tr>
                                <tr><td><code>brgy</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Barangay name (max 255 chars)</td></tr>
                                <tr><td><code>city</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>City / municipality name (max 255 chars)</td></tr>
                                <tr><td><code>province</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Province name (max 255 chars)</td></tr>
                                <tr><td><code>street</code></td><td>string</td><td><span class="badge badge-secondary">No</span></td><td>Street name (max 255 chars)</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "CUST001",
    "name": "Sample Store",
    "channel_code": "GROCERY",
    "salesman_code": "SM001",
    "address": "123 Main St, Poblacion, Makati",
    "street": "Main St",
    "brgy": "Poblacion",
    "city": "Makati",
    "province": "Metro Manila"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "CUST001",
        "name": "Sample Store",
        "address": "123 Main St, Poblacion, Makati",
        "street": "Main St",
        "brgy": "Poblacion",
        "city": "Makati",
        "province": "Metro Manila",
        "status": 1,
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z",
        "salesman": { "id": 1, "code": "SM001", ... },
        "channel": { "id": 1, "code": "GROCERY", ... }
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/customer/{id}/get</span>
                        <span class="api-desc">Retrieve a single customer by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the customer's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "CUST001",
        "name": "Sample Store",
        "address": "123 Main St, Poblacion, Makati",
        "street": "Main St",
        "brgy": "Poblacion",
        "city": "Makati",
        "province": "Metro Manila",
        "status": 1,
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z",
        "salesman": { "id": 1, "code": "SM001", "name": "Juan Dela Cruz", ... },
        "channel": { "id": 1, "code": "GROCERY", "name": "Grocery", ... }
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/customer/{id}/update</span>
                        <span class="api-desc">Update an existing customer</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">Same fields as create. <code>code</code> uniqueness check excludes the current record.</p>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "code": "CUST001",
    "name": "Sample Store Updated",
    "channel_code": "GROCERY",
    "salesman_code": "SM001",
    "address": "456 New St, Poblacion, Makati",
    "street": "New St",
    "brgy": "Poblacion",
    "city": "Makati",
    "province": "Metro Manila"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "code": "CUST001",
        "name": "Sample Store Updated",
        "address": "456 New St, Poblacion, Makati",
        "street": "New St",
        "brgy": "Poblacion",
        "city": "Makati",
        "province": "Metro Manila",
        "status": 1,
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z",
        "salesman": { "id": 1, "code": "SM001", ... },
        "channel": { "id": 1, "code": "GROCERY", ... }
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Inventory --}}
        <div id="section-inventory" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-boxes mr-2"></i>Inventory</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All inventory endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>
                <p class="text-muted small">SKU codes support product mapping. Codes prefixed with <code>FG-</code> are Free Goods (type 2) and <code>PRM-</code> as Promo (type 3). Unprefixed codes are Normal (type 1).</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/inventory</span>
                        <span class="api-desc">List inventory records for the branch (paginated, 10 per page)</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "uom": "CS",
            "inventory": 50,
            "expiry_date": "2026-01-01",
            "created_at": "2025-04-28T08:00:00.000000Z",
            "updated_at": "2025-04-28T08:00:00.000000Z",
            "product": { "id": 1, "sku_code": "SKU001", "description": "Product Name", "size": "1L", "category": "Beverages", "brand": "BrandX" },
            "location": { "id": 1, "code": "WH001", "name": "Main Warehouse", ... },
            "inventory_upload": { "date": "2025-04-01", "total_inventory": 150, ... }
        }
    ],
    "links": { ... },
    "meta": { "current_page": 1, "per_page": 10, "total": 1 }
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/inventory/create</span>
                        <span class="api-desc">Create an inventory record</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>warehouse_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Location/warehouse code (must exist in branch)</td></tr>
                                <tr><td><code>sku_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Product SKU code (mapped and validated against SMS products)</td></tr>
                                <tr><td><code>inventory_date</code></td><td>date</td><td><span class="badge badge-danger">Yes</span></td><td>Date of the inventory count (YYYY-MM-DD)</td></tr>
                                <tr><td><code>uom</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unit of measure</td></tr>
                                <tr><td><code>inventory</code></td><td>numeric</td><td><span class="badge badge-danger">Yes</span></td><td>Inventory quantity</td></tr>
                                <tr><td><code>expiry_date</code></td><td>date</td><td><span class="badge badge-secondary">No</span></td><td>Product expiry date (YYYY-MM-DD)</td></tr>
                            </tbody>
                        </table>
                        <p class="text-muted small mt-1">Returns a validation error if a record with the same upload batch, location, and product already exists.</p>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "warehouse_code": "WH001",
    "sku_code": "SKU001",
    "inventory_date": "2025-04-01",
    "uom": "CS",
    "inventory": 50,
    "expiry_date": "2026-01-01"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "uom": "CS",
        "inventory": 50,
        "expiry_date": "2026-01-01",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z",
        "product": { "id": 1, "sku_code": "SKU001", "description": "Product Name", ... },
        "location": { "id": 1, "code": "WH001", "name": "Main Warehouse", ... },
        "inventory_upload": { "date": "2025-04-01", "total_inventory": 50, ... }
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/inventory/{id}/get</span>
                        <span class="api-desc">Retrieve a single inventory record by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the inventory record's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "uom": "CS",
        "inventory": 50,
        "expiry_date": "2026-01-01",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T08:00:00.000000Z",
        "product": { "id": 1, "sku_code": "SKU001", "description": "Product Name", "size": "1L", "category": "Beverages", "brand": "BrandX" },
        "location": { "id": 1, "code": "WH001", "name": "Main Warehouse", ... },
        "inventory_upload": { "date": "2025-04-01", "total_inventory": 50, ... }
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/inventory/{id}/update</span>
                        <span class="api-desc">Update an existing inventory record</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">Same fields as create. If <code>inventory_date</code> differs from the original, the record is moved to a new (or existing) upload batch for that date.</p>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "warehouse_code": "WH001",
    "sku_code": "SKU001",
    "inventory_date": "2025-04-15",
    "uom": "CS",
    "inventory": 75,
    "expiry_date": "2026-01-01"
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "uom": "CS",
        "inventory": 75,
        "expiry_date": "2026-01-01",
        "created_at": "2025-04-28T08:00:00.000000Z",
        "updated_at": "2025-04-28T09:00:00.000000Z",
        "product": { "id": 1, "sku_code": "SKU001", ... },
        "location": { "id": 1, "code": "WH001", ... },
        "inventory_upload": { "date": "2025-04-15", "total_inventory": 75, ... }
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Sales --}}
        <div id="section-sales" class="api-section card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-chart-line mr-2"></i>Sales</h3>
            </div>
            <div class="card-body">

                <p class="text-muted small">All sales endpoints require <code>Authorization: Bearer &lt;token&gt;</code> and <code>BRANCH-KEY</code> headers.</p>
                <p class="text-muted small">SKU codes support product mapping. <code>FG-</code> prefix = Free Goods (type 2), <code>PRM-</code> prefix = Promo (type 3). Invoice numbers with <code>PSC-</code> prefix are Credit Memos. Duplicate records (same invoice, customer, product, salesman, location, date, UOM) are rejected.</p>

                {{-- List --}}
                <div class="api-endpoint">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/sales</span>
                        <span class="api-desc">List sales records for the branch (paginated, 10 per page)</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Query Parameters</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>year</code></td><td>integer</td><td><span class="badge badge-danger">Yes</span></td><td>Filter by year (e.g. <code>2025</code>)</td></tr>
                                <tr><td><code>month</code></td><td>integer</td><td><span class="badge badge-secondary">No</span></td><td>Filter by month 1–12</td></tr>
                            </tbody>
                        </table>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "data": [
        {
            "id": 1,
            "type": 1,
            "date": "2025-04-15",
            "document_number": "INV-00123",
            "category": 0,
            "uom": "CS",
            "quantity": "10",
            "price_inc_vat": "115.00",
            "amount": "1000.00",
            "amount_inc_vat": "1150.00",
            "status": 1,
            "sales_upload": { "id": 1, "sku_count": 1, "total_quantity": "10", ... },
            "customer": { "id": 1, "code": "CUST001", "name": "Sample Store", ... },
            "channel": { "id": 1, "code": "GROCERY", "name": "Grocery", ... },
            "salesman": { "id": 1, "code": "SM001", "name": "Juan Dela Cruz", ... },
            "location": { "id": 1, "code": "WH001", "name": "Main Warehouse", ... },
            "product": { "id": 1, "sku_code": "SKU001", "description": "Product Name", ... },
            "user": { "name": "Admin User", "email": "admin@example.com", "username": "admin" }
        }
    ],
    "links": { ... },
    "meta": { "current_page": 1, "per_page": 10, "total": 1 }
}</code></pre>
                    </div>
                </div>

                {{-- Create --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/sales/create</span>
                        <span class="api-desc">Create a new sales record</span>
                    </div>
                    <div class="api-endpoint-body">
                        <h6>Request Body</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>customer_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Customer code (must exist in branch)</td></tr>
                                <tr><td><code>sku_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Product SKU code (mapped and validated against SMS products)</td></tr>
                                <tr><td><code>channel_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Channel code (must exist; channel mapping applied)</td></tr>
                                <tr><td><code>salesman_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Salesman code (must exist in branch)</td></tr>
                                <tr><td><code>warehouse_code</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Location/warehouse code (must exist in branch)</td></tr>
                                <tr><td><code>date</code></td><td>date</td><td><span class="badge badge-danger">Yes</span></td><td>Transaction date (YYYY-MM-DD)</td></tr>
                                <tr><td><code>invoice_number</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Invoice number. Prefix <code>PSC-</code> for credit memos.</td></tr>
                                <tr><td><code>uom</code></td><td>string</td><td><span class="badge badge-danger">Yes</span></td><td>Unit of measure</td></tr>
                                <tr><td><code>quantity</code></td><td>numeric</td><td><span class="badge badge-danger">Yes</span></td><td>Quantity sold</td></tr>
                                <tr><td><code>price_inc_vat</code></td><td>numeric</td><td><span class="badge badge-danger">Yes</span></td><td>Unit price inclusive of VAT</td></tr>
                                <tr><td><code>amount</code></td><td>numeric</td><td><span class="badge badge-danger">Yes</span></td><td>Total amount excluding VAT</td></tr>
                                <tr><td><code>amount_inc_vat</code></td><td>numeric</td><td><span class="badge badge-danger">Yes</span></td><td>Total amount inclusive of VAT</td></tr>
                            </tbody>
                        </table>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "customer_code": "CUST001",
    "sku_code": "SKU001",
    "channel_code": "GROCERY",
    "salesman_code": "SM001",
    "warehouse_code": "WH001",
    "date": "2025-04-15",
    "invoice_number": "INV-00123",
    "uom": "CS",
    "quantity": 10,
    "price_inc_vat": 115.00,
    "amount": 1000.00,
    "amount_inc_vat": 1150.00
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "type": 1,
        "date": "2025-04-15",
        "document_number": "INV-00123",
        "category": 0,
        "uom": "CS",
        "quantity": "10",
        "price_inc_vat": "115.00",
        "amount": "1000.00",
        "amount_inc_vat": "1150.00",
        "status": 1,
        "sales_upload": { "id": 1, "sku_count": 1, "total_quantity": "10", ... },
        "customer": { "id": 1, "code": "CUST001", ... },
        "channel": { "id": 1, "code": "GROCERY", ... },
        "salesman": { "id": 1, "code": "SM001", ... },
        "location": { "id": 1, "code": "WH001", ... },
        "product": { "id": 1, "sku_code": "SKU001", ... },
        "user": { "name": "Admin User", "username": "admin", ... }
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Show --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-get">GET</span>
                        <span class="api-path">/api/sales/{id}/get</span>
                        <span class="api-desc">Retrieve a single sales record by ID</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2"><code>{id}</code> — the sales record's numeric ID. No request body.</p>
                        <h6>Example Response</h6>
                        <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "type": 1,
        "date": "2025-04-15",
        "document_number": "INV-00123",
        "category": 0,
        "uom": "CS",
        "quantity": "10",
        "price_inc_vat": "115.00",
        "amount": "1000.00",
        "amount_inc_vat": "1150.00",
        "status": 1,
        "sales_upload": { "id": 1, "sku_count": 1, "total_quantity": "10", ... },
        "customer": { "id": 1, "code": "CUST001", "name": "Sample Store", ... },
        "channel": { "id": 1, "code": "GROCERY", "name": "Grocery", ... },
        "salesman": { "id": 1, "code": "SM001", "name": "Juan Dela Cruz", ... },
        "location": { "id": 1, "code": "WH001", "name": "Main Warehouse", ... },
        "product": { "id": 1, "sku_code": "SKU001", "description": "Product Name", ... },
        "user": { "name": "Admin User", "email": "admin@example.com", "username": "admin" }
    }
}</code></pre>
                    </div>
                </div>

                {{-- Update --}}
                <div class="api-endpoint mt-3">
                    <div class="api-endpoint-header">
                        <span class="badge-method badge-post">POST</span>
                        <span class="api-path">/api/sales/{id}/update</span>
                        <span class="api-desc">Update an existing sales record</span>
                    </div>
                    <div class="api-endpoint-body">
                        <p class="text-muted small mb-2">Same fields as create. Updating the <code>date</code> triggers a sales report regeneration for the original date's period.</p>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6>Example Request</h6>
                                <pre class="api-code"><code>{
    "customer_code": "CUST001",
    "sku_code": "SKU001",
    "channel_code": "GROCERY",
    "salesman_code": "SM001",
    "warehouse_code": "WH001",
    "date": "2025-04-20",
    "invoice_number": "INV-00123",
    "uom": "CS",
    "quantity": 12,
    "price_inc_vat": 115.00,
    "amount": 1200.00,
    "amount_inc_vat": 1380.00
}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Example Response</h6>
                                <pre class="api-code"><code>{
    "success": true,
    "data": {
        "id": 1,
        "type": 1,
        "date": "2025-04-20",
        "document_number": "INV-00123",
        "category": 0,
        "uom": "CS",
        "quantity": "12",
        "price_inc_vat": "115.00",
        "amount": "1200.00",
        "amount_inc_vat": "1380.00",
        "status": 1,
        "sales_upload": { ... },
        "customer": { "id": 1, "code": "CUST001", ... },
        "channel": { ... },
        "salesman": { ... },
        "location": { ... },
        "product": { ... },
        "user": { ... }
    }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .api-section {
            margin-bottom: 1.5rem;
            scroll-margin-top: 70px;
        }
        .api-toc-nav .nav-link {
            padding: 0.4rem 1rem;
            color: #495057;
            border-left: 3px solid transparent;
            font-size: 0.875rem;
        }
        .api-toc-nav .nav-link:hover {
            color: #007bff;
            border-left-color: #007bff;
            background: #f8f9fa;
        }
        .api-toc-nav .nav-link.active {
            color: #007bff;
            border-left-color: #007bff;
            font-weight: 600;
        }
        .api-endpoint {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
        }
        .api-endpoint-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            flex-wrap: wrap;
        }
        .api-endpoint-body {
            padding: 1rem;
        }
        .badge-method {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 4px;
            letter-spacing: 0.5px;
            min-width: 50px;
            text-align: center;
        }
        .badge-get  { background: #cfe2ff; color: #084298; }
        .badge-post { background: #d1e7dd; color: #0a3622; }
        .api-path {
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: 600;
            color: #212529;
        }
        .api-desc {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .api-base-url {
            display: inline-block;
            background: #212529;
            color: #f8f9fa;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .api-code {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 0.8rem 1rem;
            border-radius: 4px;
            font-size: 0.8rem;
            overflow-x: auto;
            margin: 0;
            white-space: pre;
        }
        .api-subsection-title {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.5rem;
        }
        .api-endpoint-body h6 {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
            margin-top: 0.75rem;
        }
        .api-endpoint-body h6:first-child { margin-top: 0; }
        .table-sm td, .table-sm th { font-size: 0.85rem; }
        @media (max-width: 767.98px) {
            .api-toc {
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
            const tocCard = document.querySelector('.api-toc');
            const tocCol = document.querySelector('.api-toc-col');
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
            window.addEventListener('resize', function () {
                syncTocWidth();
                updateTocTop();
            });

            const contentHeader = document.querySelector('.content-header');
            function updateTocTop() {
                if (window.innerWidth < 768) { return; }
                const headerBottom = contentHeader ? contentHeader.getBoundingClientRect().bottom : 0;
                tocCard.style.top = headerBottom <= 57 ? '80px' : '162px';
            }
            updateTocTop();
            window.addEventListener('scroll', updateTocTop);

            const sections = document.querySelectorAll('.api-section');
            const links = document.querySelectorAll('.api-toc-nav .nav-link');

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        links.forEach(l => l.classList.remove('active'));
                        const active = document.querySelector(`.api-toc-nav a[href="#${entry.target.id}"]`);
                        if (active) { active.classList.add('active'); }
                    }
                });
            }, { rootMargin: '-60px 0px -70% 0px' });

            sections.forEach(s => observer.observe(s));
        });
    </script>
@stop

@extends('adminlte::page')

@section('title', 'Hub Admin')

@section('content_header')
    <div class="page-header-bar">
        <div class="page-header-left">
            <div class="page-header-info">
                <h1 class="page-header-title">Hub Admin</h1>
                <span class="page-header-sub">
                    <i class="fa fa-shield-alt mr-1"></i>
                    SSO Administration
                </span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-user-shield mr-2"></i>Admin Access Confirmed</h3>
                    </div>
                    <div class="card-body">
                        <p>You are logged in as an <strong>admin</strong> via Hub SSO.</p>
                        <table class="table table-bordered">
                            <tr>
                                <th>Name</th>
                                <td>{{ auth()->user()->name }}</td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td>{{ auth()->user()->username }}</td>
                            </tr>
                            <tr>
                                <th>Hub User ID</th>
                                <td>{{ auth()->user()->hub_user_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Roles</th>
                                <td>{{ auth()->user()->getRoleNames()->implode(', ') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

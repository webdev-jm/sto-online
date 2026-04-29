@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>Dashboard</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{ route('home') }}" class="btn-change-branch">
                VIEW ACCOUNTS
            </a>
        </div>
    </div>
@stop

@section('content')
    <livewire:dashboard.header />

    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-link mr-2"></i>Hub Identity</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr>
                                <th class="pl-3">Name</th>
                                <td>{{ auth()->user()->name }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Username</th>
                                <td>{{ auth()->user()->username }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Hub User ID</th>
                                <td>{{ auth()->user()->hub_user_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Roles</th>
                                <td>{{ auth()->user()->getRoleNames()->implode(', ') ?: '—' }}</td>
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
    <style>

    </style>
@stop

@section('js')
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/mouse-wheel-zoom.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
@stop

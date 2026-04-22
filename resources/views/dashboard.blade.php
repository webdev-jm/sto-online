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

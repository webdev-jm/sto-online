@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>REPORTS</h1>
        </div>
        <div class="col-lg-6 text-right">
        </div>
    </div>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REPORTS</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">DATE FROM</label>
                        <input type="date" class="form-control">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">DATE TO</label>
                        <input type="date" class="form-control">
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-right p-1">
            <button class="btn btn-primary">
                <i class="fa fa-filter fa-sm mr-1"></i>
                FILTER
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES DATA</h3>
        </div>
        <div class="card-body">

        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    $(function() {

    });
</script>
@stop

@extends('adminlte::page')

@section('title', 'Templates')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>TEMPLATES</h1>
    </div>
    <div class="col-lg-6 text-right">
        
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-3">
            <livewire:template.template-list/>
        </div>

        <div class="col-9">
            <livewire:template.template-detail/>
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

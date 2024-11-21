@extends('adminlte::page')

@section('title', 'RTV - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - RTV</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">CREAT RTV</h3>
            <div class="card-tools">
            </div>
        </div>
        <div class="card-body">
            
            <div class="row">

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">RTV NUMBER</label>
                        <input type="text" class="form-control">
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">DOCUMENT NUMBER</label>
                        <input type="text" class="form-control">
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">SHIP DATE</label>
                        <input type="date" class="form-control">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="">REASON</label>
                        <textarea class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">SHIP TO NAME</label>
                        <input type="text" class="form-control">
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">SHIP TO ADDRESS</label>
                        <input type="text" class="form-control">
                    </div>
                </div>

            </div>

            <hr>
            <livewire:return-to-vendor.create/>

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm">
                <i class="fa fa-plus mr-1"></i>
                ADD RTV
            </button>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function() {

    });
</script>
@stop

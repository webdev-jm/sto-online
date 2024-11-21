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
{!! Form::open(['method' => 'POST', 'route' => ['rtv.store'], 'id' => 'add_rtv', 'autocomplete' => 'off']) !!}
{!! Form::close() !!}

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
                        <input type="text" class="form-control {{ $errors->has('rtv_number') ? ' is-invalid' : '' }}" name="rtv_number" form="add_rtv">
                        <small class="text-danger">{{$errors->first('rtv_number')}}</small>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">DOCUMENT NUMBER</label>
                        <input type="text" class="form-control{{ $errors->has('document_number') ? ' is-invalid' : '' }}" name="document_number" form="add_rtv">
                        <small class="text-danger">{{$errors->first('document_number')}}</small>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">SHIP DATE</label>
                        <input type="date" class="form-control{{ $errors->has('ship_date') ? ' is-invalid' : '' }}" name="ship_date" form="add_rtv">
                        <small class="text-danger">{{$errors->first('ship_date')}}</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="">REASON</label>
                        <textarea class="form-control{{ $errors->has('reason') ? ' is-invalid' : '' }}" name="reason" form="add_rtv"></textarea>
                        <small class="text-danger">{{$errors->first('reason')}}</small>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">SHIP TO NAME</label>
                        <input type="text" class="form-control{{ $errors->has('ship_to_name') ? ' is-invalid' : '' }}" name="ship_to_name" form="add_rtv">
                        <small class="text-danger">{{$errors->first('ship_to_name')}}</small>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">SHIP TO ADDRESS</label>
                        <input type="text" class="form-control{{ $errors->has('ship_to_address') ? ' is-invalid' : '' }}" name="ship_to_address" form="add_rtv">
                        <small class="text-danger">{{$errors->first('ship_to_address')}}</small>
                    </div>
                </div>

            </div>

            <hr>
            <livewire:return-to-vendor.create/>

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" type="submit" form="add_rtv">
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

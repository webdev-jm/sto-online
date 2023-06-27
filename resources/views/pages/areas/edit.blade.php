@extends('adminlte::page')

@section('title', 'Edit Area - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - EDIT AREA</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('area.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('area.show', encrypt($area->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>Details</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['area.update', encrypt($area->id)], 'id' => 'update_area', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT AREA</h3>
        </div>
        <div class="card-body">

            <div class="row">
                
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Area Code') !!}
                        {!! Form::text('code', $area->code, ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'update_area']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Area Name') !!}
                        {!! Form::text('name', $area->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_area']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

            </div>
            
        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Area', ['class' => 'btn btn-primary', 'form' => 'update_area']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop

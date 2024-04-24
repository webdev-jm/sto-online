@extends('adminlte::page')

@section('title', 'Add District - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - ADD DISTRICT</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('district.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['district.store'], 'id' => 'add_district', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ADD DISTRICT</h3>
        </div>
        <div class="card-body">

            <div class="row">
                
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('district_code', 'District Code') !!}
                        {!! Form::text('district_code', '', ['class' => 'form-control'.($errors->has('district_code') ? ' is-invalid' : ''), 'form' => 'add_district']) !!}
                        <p class="text-danger">{{$errors->first('district_code')}}</p>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-12">
                    <label>AREAS</label>
                    @if($errors->has('areas'))
                    <span class="badge badge-danger ml-1">Required</span>
                    @endif
                </div>
                @foreach($areas as $area)
                    <div class="col-12">
                        <div class="row">
                            @foreach($areas as $area)
                                <div class="col-12">
                                    <div class="custom-control custom-switch">
                                        {!! Form::checkbox('areas[]', $area->id, false, ['class' => 'custom-control-input', 'id' => 'area'.$area->id, 'form' => 'add_district']) !!}
                                        {!! Form::label('area'.$area->id, $area->name, ['class' => 'custom-control-label']) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Add District', ['class' => 'btn btn-primary', 'form' => 'add_district']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop

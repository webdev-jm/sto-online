@extends('adminlte::page')

@section('title', 'Add Salesman - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - ADD SALESMAN</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('salesman.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['salesman.store'], 'id' => 'add_salesman', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ADD SALESMAN</h3>
        </div>
        <div class="card-body">

            <div class="row">
                
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Salesman Code') !!}
                        {!! Form::text('code', '', ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'add_salesman']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Salesman Name') !!}
                        {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_salesman']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('district_id', 'District') !!}
                        {!! Form::select('district_id', $districts, NULL, ['class' => 'form-control'.($errors->has('district_id') ? ' is-invalid' : ''), 'form' => 'add_salesman']) !!}
                        <p class="text-danger">{{$errors->first('district_id')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('type', 'Type of Salesman') !!}
                        {!! Form::select('type', $salesman_types_arr, NULL, ['class' => 'form-control'.($errors->has('type') ? ' is-invalid' : ''), 'form' => 'add_salesman']) !!}
                        <p class="text-danger">{{$errors->first('type')}}</p>
                    </div>
                </div>

            </div>
            
        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Add Salesman', ['class' => 'btn btn-primary', 'form' => 'add_salesman']) !!}
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

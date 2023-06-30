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

            </div>

            <hr>

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>Areas</label> 
                        @if($errors->has('area_ids'))
                            <span class="badge badge-danger">{{$errors->first('area_ids')}}</span>
                        @endif
                        {!! Form::hidden('area_ids', '', ['id' => 'area_ids', 'form' => 'add_salesman']) !!}
                        <br>
                        @foreach($areas as $area)
                            <button class="btn btn-default mb-2 btn-area" data-id="{{$area->id}}">[{{$area->code}}] {{$area->name}}</button>
                        @endforeach
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
        // AREA
        $('body').on('click', '.btn-area', function(e) {
            e.preventDefault();
            $(this).toggleClass('btn-success').toggleClass('btn-default');

            // get all selected
            var area_ids = [];
            $('body').find('.btn-area').each(function() {
                var id = $(this).data('id');
                if($(this).hasClass('btn-success')) {
                    area_ids.push(id);
                }
            });

            var areas = area_ids.join(',');
            $('#area_ids').val(areas);
        });
    });
</script>
@stop

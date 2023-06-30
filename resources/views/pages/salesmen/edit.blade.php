@extends('adminlte::page')

@section('title', 'Edit Salesman - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - EDIT SALESMAN</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('salesman.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('salesman.show', encrypt($salesman->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>Details</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['salesman.update', encrypt($salesman->id)], 'id' => 'update_salesman', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT SALESMAN</h3>
        </div>
        <div class="card-body">

            <div class="row">
                
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Area Code') !!}
                        {!! Form::text('code', $salesman->code, ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'update_salesman']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Area Name') !!}
                        {!! Form::text('name', $salesman->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_salesman']) !!}
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
                        {!! Form::hidden('area_ids', implode(',', $salesman_areas), ['id' => 'area_ids', 'form' => 'update_salesman']) !!}
                        <br>
                        @foreach($areas as $area)
                            <button class="btn {{in_array($area->id, $salesman_areas) ? 'btn-success' : 'btn-default'}} mb-2 btn-area" data-id="{{$area->id}}">[{{$area->code}}] {{$area->name}}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            
        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Salesman', ['class' => 'btn btn-primary', 'form' => 'update_salesman']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function() {
        // AREAS
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

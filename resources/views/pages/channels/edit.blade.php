@extends('adminlte::page')

@section('title', 'Edit Channel - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - EDIT CHANNELS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('channel.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('channel.show', encrypt($channel->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>Details</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['channel.update', encrypt($channel->id)], 'id' => 'update_channel', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT CHANNEL</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                {{-- CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Channel Code') !!}
                        {!! Form::text('code', $channel->code, ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'update_channel']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Channel Name') !!}
                        {!! Form::text('name', $channel->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_channel']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Channel', ['class' => 'btn btn-primary', 'form' => 'update_channel']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop

@extends('adminlte::page')

@section('title', 'Edit Customer - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - EDIT CUSTOMER</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('customer.show', encrypt($customer->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>DETAILS</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['customer.update', encrypt($customer->id)], 'id' => 'update_customer', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT CUSTOMER</h3>
        </div>
        <div class="card-body">
            
            <div class="row">

                {{-- CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Customer Code') !!}
                        {!! Form::text('code', $customer->code, ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Customer Name') !!}
                        {!! Form::text('name', $customer->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

                {{-- SALESMAN --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('salesman_id', 'Salesman') !!}
                        {!! Form::select('salesman_id', $salesmen, $customer->salesman_id, ['class' => 'form-control'.($errors->has('salesman_id') ? ' is-invalid' : ''), 'form' => 'update_customer', 'placeholder' => '- select salesman -']) !!}
                        <p class="text-danger">{{$errors->first('salesman_id')}}</p>
                    </div>
                </div>

                {{-- CHANNEL --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('channel_id', 'Channel') !!}
                        {!! Form::select('channel_id', $channels, $customer->channel_id, ['class' => 'form-control'.($errors->has('channel_id') ? ' is-invalid' : ''), 'form' => 'update_customer', 'placeholder' => '- select channel -']) !!}
                        <p class="text-danger">{{$errors->first('channel_id')}}</p>
                    </div>
                </div>

            </div>

            <div class="row">
                {{-- ADDRESS --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('address', 'Address') !!}
                        {!! Form::text('address', $customer->address, ['class' => 'form-control'.($errors->has('address') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('address')}}</p>
                    </div>
                </div>
                {{-- STREET --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('street', 'Street') !!}
                        {!! Form::text('street', $customer->street, ['class' => 'form-control'.($errors->has('street') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('street')}}</p>
                    </div>
                </div>
                {{-- BARANGAY --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('barangay', 'Barangay') !!}
                        {!! Form::text('barangay', $customer->brgy, ['class' => 'form-control'.($errors->has('barangay') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('barangay')}}</p>
                    </div>
                </div>
                {{-- CITY --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('city', 'City/Town') !!}
                        {!! Form::text('city', $customer->city, ['class' => 'form-control'.($errors->has('city') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('city')}}</p>
                    </div>
                </div>
                {{-- PROVINCE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('province', 'Province') !!}
                        {!! Form::text('province', $customer->province, ['class' => 'form-control'.($errors->has('province') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('province')}}</p>
                    </div>
                </div>
                {{-- POSTAL CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('postal_code', 'Postal Code') !!}
                        {!! Form::text('postal_code', $customer->postal_code, ['class' => 'form-control'.($errors->has('postal_code') ? ' is-invalid' : ''), 'form' => 'update_customer']) !!}
                        <p class="text-danger">{{$errors->first('postal_code')}}</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Customer', ['class' => 'btn btn-primary', 'form' => 'update_customer']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop

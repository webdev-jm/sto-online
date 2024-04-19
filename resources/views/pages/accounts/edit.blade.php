@extends('adminlte::page')

@section('title', 'Account - Add')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ACCOUNTS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('account.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            <a href="{{route('account.show', encrypt($account->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>DETAILS</a>
        </div>
    </div>
@stop

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['account.update', encrypt($account->id)], 'id' => 'update_account', 'autocomplete' => 'off']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT ACCOUNT</h3>
        </div>
        <div class="card-body">

            <div class="row">

                {{-- SMS ACCOUNT --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('sms_account_id', 'SMS Account') !!}
                        {!! Form::select('sms_account_id', [], NULL, ['class' => 'form-control'.($errors->has('sms_account_id') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                        <small class="text-danger">{{$errors->first('sms_account_id')}}</small>
                    </div>
                </div>

                {{-- ACCOUNT CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('account_code', 'Account Code') !!}
                        {!! Form::text('account_code', $account->account_code, ['class' => 'form-control'.($errors->has('account_code') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                        <small class="text-danger">{{$errors->first('account_code')}}</small>
                    </div>
                </div>

                {{-- ACCOUNT NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('account_name', 'Account Name') !!}
                        {!! Form::text('account_name', $account->account_name, ['class' => 'form-control'.($errors->has('account_name') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                        <small class="text-danger">{{$errors->first('account_name')}}</small>
                    </div>
                </div>

                {{-- SHORT NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('short_name', 'Short Name') !!}
                        {!! Form::text('short_name', $account->short_name, ['class' => 'form-control'.($errors->has('short_name') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                        <small class="text-danger">{{$errors->first('short_name')}}</small>
                    </div>
                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', ['class' => 'form-control'.($errors->has('password') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                        <p class="text-danger mt-1">{{$errors->first('password')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'Confirm Password') !!}
                        {!! Form::password('password_confirmation', ['class' => 'form-control'.($errors->has('password') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Account', ['class' => 'btn btn-primary btn-sm', 'form' => 'update_account']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('plugins.Select2', true)

@section('js')
<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#sms_account_id').select2({
            ajax: { 
                url: '{{route("account.ajax")}}',
                type: "POST",
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });

        var user_select = $('#sms_account_id');
        $.ajax({
            type:'GET',
            url: '/account/get-ajax/{{$account->sms_account_id}}'
        }).then(function(data) {
            var option = new Option('['+data.account_code+'] '+data.short_name, data.id, true, true);
            user_select.append(option).trigger('change');

            user_select.trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        });
    });
</script>
@stop

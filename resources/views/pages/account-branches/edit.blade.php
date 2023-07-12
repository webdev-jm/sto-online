@extends('adminlte::page')

@section('title', 'Account Branch - Edit')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ACCOUNT BRANCHES</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('account-branch.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            <a href="{{route('account-branch.show', encrypt($account_branch->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>Details</a>
        </div>
    </div>
@stop

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['account-branch.update', encrypt($account_branch->id)], 'id' => 'update_branch', 'autocomplete' => 'off']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT BRANCH</h3>
        </div>
        <div class="card-body">

            <div class="row">

                {{-- ACCOUNT --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('account_id', 'Account') !!}
                        {!! Form::select('account_id', [], NULL, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'update_branch']) !!}
                        <small class="text-danger">{{$errors->first('account_id')}}</small>
                    </div>
                </div>

                {{-- CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Code') !!}
                        {!! Form::text('code', $account_branch->code, ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'update_branch']) !!}
                        <small class="text-danger">{{$errors->first('code')}}</small>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Name') !!}
                        {!! Form::text('name', $account_branch->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_branch']) !!}
                        <small class="text-danger">{{$errors->first('name')}}</small>
                    </div>
                </div>

                {{-- AREA --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('bevi_area_id', 'Area') !!}
                        {!! Form::select('bevi_area_id', $areas, $account_branch->bevi_area_id, ['class' => 'form-control'.($errors->has('bevi_area_id') ? ' is-invalid' : ''), 'form' => 'update_branch', 'placeholder' => '- select area -']) !!}
                        <small class="text-danger">{{$errors->first('bevi_area_id')}}</small>
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Branch', ['class' => 'btn btn-primary btn-sm', 'form' => 'update_branch']) !!}
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

        $('#account_id').select2({
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

        var user_select = $('#account_id');
        $.ajax({
            type:'GET',
            url: '/account/get-ajax/{{$account_branch->account_id}}'
        }).then(function(data) {
            console.log(data);
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

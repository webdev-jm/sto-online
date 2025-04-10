@extends('adminlte::page')

@section('title', 'User - Add')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>USERS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('user.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
        </div>
    </div>
@stop

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['user.store'], 'id' => 'add_user', 'autocomplete' => 'off']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ADD USER</h3>
        </div>
        <div class="card-body">

            <label class="mb-0 text-primary">GENERAL INFORMATION</label>
            <hr class="mt-0 bg-primary">

            <div class="row">

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Name') !!}
                        {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('name')}}</p>
                    </div>
                </div>

                {{-- EMAIL --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('email', 'Email') !!}
                        {!! Form::email('email', '', ['class' => 'form-control'.($errors->has('email') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('email')}}</p>
                    </div>
                </div>

                {{-- ACCOUNT --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('account_id', 'Account') !!}
                        {!! Form::select('account_id', [], NULL, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                        <small class="text-danger">{{$errors->first('account_id')}}</small>
                    </div>
                </div>

                {{-- TYPE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('type', 'Type') !!}
                        {!! Form::select('type', $types_arr, NULL, ['class' => 'form-control'.($errors->has('type') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                        <small class="text-danger">{{$errors->first('type')}}</small>
                    </div>
                </div>

            </div>

            <label class="mb-0 text-primary">LOGIN</label>
            <hr class="mt-0 bg-primary">

            <div class="row">

                {{-- USERNAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('username', 'Username') !!}
                        {!! Form::text('username', '', ['class' => 'form-control'.($errors->has('username') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('username')}}</p>
                    </div>
                </div>

                {{-- PASSWORD --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', ['class' => 'form-control'.($errors->has('password') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('password')}}</p>
                    </div>
                </div>

                {{-- CONFIRM PASSWORD --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'Confirm Password') !!}
                        {!! Form::password('password_confirmation', ['class' => 'form-control'.($errors->has('password') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    </div>
                </div>

            </div>

            {{-- ROLES --}}
            <label class="mb-0 text-primary">ROLES</label>
            @if($errors->has('role_ids'))
                <span class="badge badge-danger">required</span>
            @endif
            <hr class="mt-0 bg-primary">
            {!! Form::hidden('role_ids', '', ['form' => 'add_user', 'id' => 'role_ids']) !!}

            <div class="row">
                <div class="col-12">
                    @foreach($roles as $role)
                        <button class="btn btn-default btn-role" data-id="{{$role->id}}">{{$role->name}}</button>
                    @endforeach
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Add User', ['class' => 'btn btn-primary btn-sm', 'form' => 'add_user']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('Select2', true)

@section('js')
<script>
    $(function() {
        $('.select2').select2();

        // ROLES
        $('body').on('click', '.btn-role', function(e) {
            e.preventDefault();
            $(this).toggleClass('btn-success').toggleClass('btn-default');

            // get all selected
            var role_ids = [];
            $('body').find('.btn-role').each(function() {
                var id = $(this).data('id');
                if($(this).hasClass('btn-success')) {
                    role_ids.push(id);
                }
            });

            var roles = role_ids.join(',');
            $('#role_ids').val(roles);
        });

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

    });
</script>
@stop

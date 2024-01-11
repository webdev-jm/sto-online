@extends('adminlte::page')

@section('title', 'CUSTOMER UBO')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>UBO JOB</h1>
    </div>
    <div class="col-lg-6 text-right">
    </div>
</div>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UBO JOB</h3>
        </div>
        <div class="card-body">

            <div class="row">
                
                {{-- ACCOUNT --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('account_id', 'Account') !!}
                        {!! Form::select('account_id', [], NULL, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                        <small class="text-danger">{{$errors->first('account_id')}}</small>
                    </div>
                </div>

            </div>

            @can('customer ubo job')
            <div class="row">
                <div class="col-12">
                    <livewire:ubo-job.run />
                </div>
            </div>
            @endcan
            
        </div>
        <div class="card-footer">
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
        }).on('select2:select', function(e) {
            var account = e.params.data;
            Livewire.emit('selectAccount', account.id);
        });
    });
</script>
@stop

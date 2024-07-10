@extends('adminlte::page')

@section('title', 'Account - Details')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ACCOUNTS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('account.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            @can('account edit')
                <a href="{{route('account.edit', encrypt($account->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>EDIT</a>
            @endcan
            @can('account delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($account->id)}}"><i class="fa fa-trash-alt mr-1"></i>DELETE</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="row">

        <div class="col-lg-3">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">ACCOUNT DETAILS</h3>
                </div>
                <div class="card-body py-0">

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>ACCOUNT CODE</b>
                            <span class="float-right">{{$account->account_code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>ACCOUNT NAME</b>
                            <span class="float-right">{{$account->account_name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>SHORT NAME</b>
                            <span class="float-right">{{$account->short_name ?? '-'}}</span>
                        </li>
                    </ul>

                </div>
                <div class="card-footer">

                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">TEMPLATES</h3>
                    <div class="card-tools">
                        <a href="" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus mr-1"></i>
                            ADD TEMPLATE
                        </a>
                    </div>
                </div>
                <div class="card-body">

                </div>
                <div class="card-footer">
                    
                </div>
            </div>
        </div>

    </div>

    @can('account branch delete')
        <div class="modal fade" id="modal-delete">
            <div class="modal-dialog">
                <livewire:confirm-delete/>
            </div>
        </div>
    @endcan
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    @can('account branch delete')
        <script>
            $(function() {
                $('body').on('click', '.btn-delete', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    Livewire.emit('setDeleteModel', 'AccountBranch', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan   
@stop

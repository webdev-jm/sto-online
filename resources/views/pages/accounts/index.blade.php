@extends('adminlte::page')

@section('title', 'Accounts')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>ACCOUNTS</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('account create')
            <a href="{{route('account.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Account</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['account.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ACCOUNT LIST</h3>
        </div>
        <div class="card-body">

            <div class="row mb-1">
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('search', 'Search') !!}
                        {!! Form::text('search', $search, ['class' => 'form-control', 'form' => 'search_form', 'placeholder' => 'Search']) !!}
                    </div>
                </div>
            </div>

            <b>{{$accounts->total()}} total result{{$accounts->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($accounts as $account)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$account->account_code}}</p>
                            <b>ACCOUNT CODE</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$account->account_name}}</p>
                            <b>ACCOUNT NAME</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$account->short_name}}</p>
                            <b>SHORT NAME</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">
                                <a href="{{route('account.show', encrypt($account->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('account edit')
                                    <a href="{{route('account.edit', encrypt($account->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('account delete')
                                    <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($account->id)}}">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                @endcan
                            </p>
                            <b>ACTION</b>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>

        </div>
        <div class="card-footer">
            {{$accounts->links()}}
        </div>
    </div>

    @can('account delete')
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
                    Livewire.emit('setDeleteModel', 'Account', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop

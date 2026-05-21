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

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ACCOUNT CODE</th>
                            <th>ACCOUNT NAME</th>
                            <th>SHORT NAME</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                        <tr>
                            <td class="align-middle font-weight-bold">{{$account->account_code}}</td>
                            <td class="align-middle">{{$account->account_name}}</td>
                            <td class="align-middle">{{$account->short_name}}</td>
                            <td class="align-middle text-center text-nowrap">
                                <a href="{{route('account.show', encrypt($account->id))}}" class="btn btn-info btn-xs" title="View details">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('account edit')
                                    <a href="{{route('account.edit', encrypt($account->id))}}" class="btn btn-success btn-xs" title="Edit">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('account delete')
                                    <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($account->id)}}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$accounts->links(data: ['scrollTo' => false])}}
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
                    Livewire.dispatch('setDeleteModel', { type: 'Account', model_id: id });
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop

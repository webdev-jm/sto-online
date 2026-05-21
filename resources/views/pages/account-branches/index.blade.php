@extends('adminlte::page')

@section('title', 'Account Branches')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>ACCOUNT BRANCHES</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('account branch create')
            <a href="{{route('account-branch.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Branch</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['account-branch.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">BRANCHES LIST</h3>
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

            <b>{{$account_branches->total()}} total result{{$account_branches->total() > 1 ? 's' : ''}}</b>

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ACCOUNT</th>
                            <th>CODE</th>
                            <th>NAME</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account_branches as $account_branch)
                        <tr>
                            <td class="align-middle">{{$account_branch->account->account_code}} - {{$account_branch->account->short_name ?? '-'}}</td>
                            <td class="align-middle font-weight-bold">{{$account_branch->code}}</td>
                            <td class="align-middle">{{$account_branch->name}}</td>
                            <td class="align-middle text-center text-nowrap">
                                <a href="{{route('account-branch.show', encrypt($account_branch->id))}}" class="btn btn-info btn-xs" title="View details">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('account branch edit')
                                    <a href="{{route('account-branch.edit', encrypt($account_branch->id))}}" class="btn btn-success btn-xs" title="Edit">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('account branch delete')
                                    <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($account_branch->id)}}" title="Delete">
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
            {{$account_branches->links(data: ['scrollTo' => false])}}
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
                    Livewire.dispatch('setDeleteModel', { type: 'AccountBranch', model_id: id });
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop

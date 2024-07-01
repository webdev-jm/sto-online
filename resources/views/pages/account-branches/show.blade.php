@extends('adminlte::page')

@section('title', 'Account Branch - Details')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ACCOUNT BRANCHES</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('account-branch.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            @can('account branch edit')
                <a href="{{route('account-branch.edit', encrypt($account_branch->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit</a>
            @endcan
            @can('account branch delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($account_branch->id)}}"><i class="fa fa-trash-alt mr-1"></i>Delete</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="row">

        <div class="col-lg-5">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">BRANCH DETAILS</h3>
                </div>
                <div class="card-body py-0">

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>ACCOUNT</b>
                            <span class="float-right">{{$account_branch->account->short_name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>CODE</b>
                            <span class="float-right">{{$account_branch->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>NAME</b>
                            <span class="float-right">{{$account_branch->name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>AREA</b>
                            <span class="float-right">
                                @if(!empty($account_branch->bevi_area))
                                    [{{$account_branch->bevi_area->code ?? '-'}}] {{$account_branch->bevi_area->name ?? '-'}}
                                @else
                                    -
                                @endif
                            </span>
                        </li>
                    </ul>

                </div>
                <div class="card-footer">

                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Users</h3>
                </div>
                <div class="card-body py-0">
                    <ul class="list-group list-group-unbordered">
                        @if(!empty($account_branch->users->count()))
                            @foreach($account_branch->users as $user)
                            <li class="list-group-item p-1">
                                <b>{{$user->name}}</b>
                                <span class="float-right">
                                    {{$user->email}}
                                </span>
                            </li>
                            @endforeach
                        @else
                            <li class="list-group-item text-center">
                                No data available.
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="card-footer">

                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">TOKEN</h3>
                    <div class="card-tools">
                        @can('account branch generate token')
                        <a href="{{route('account-branch.generateToken', encrypt($account_branch->id))}}" class="btn btn-primary btn-sm">
                            <i class="fa fa-refresh mr-1"></i>
                            Generate New Token
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <h4>TOKEN:</h4> <b class="border p-2" id="token-text">{{$account_branch->branch_token ?? "-"}}</b>
                    <a href="#" class="btn btn-secondary" id="btn-copy">
                        <i class="fa fa-copy mr-1"></i>
                        Copy
                    </a>
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

                $('body').on('click', '#btn-copy', function(e) {
                    e.preventDefault();
                    var token = $('#token-text').text();
                    
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(token)
                            .then(function() {
                                alert("Text copied to clipboard!");
                            })
                            .catch(function(error) {
                                console.error("Failed to copy text: ", error);
                                alert("Failed to copy text to clipboard.");
                            });
                    } else {
                        // Fallback: Create a temporary textarea to copy the text
                        var textarea = document.createElement('textarea');
                        textarea.value = token;
                        document.body.appendChild(textarea);
                        textarea.select();
                        try {
                            document.execCommand('copy');
                            alert("Text copied to clipboard!");
                        } catch (error) {
                            console.error("Failed to copy text: ", error);
                            alert("Failed to copy text to clipboard.");
                        }
                        document.body.removeChild(textarea);
                    }
                });
            });
        </script>
    @endcan
@stop

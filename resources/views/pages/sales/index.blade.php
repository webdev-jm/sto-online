@extends('adminlte::page')

@section('title', 'Sales - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - SALES</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Menu</a>
        @can('sales upload')
            <a href="{{route('sales.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-upload mr-1"></i>Upload Sales</a>
        @endcan
        {{-- <a href="{{route('sales.dashboard')}}" class="btn btn-success btn-sm"><i class="fa fa-tachometer-alt mr-1"></i>Sales Dashboard</a> --}}
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['sales.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES UPLOADS</h3>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-lg-4">
                    {!! Form::label('search', 'Search') !!}
                    {!! Form::text('search', $search, ['class' => 'form-control', 'form' => 'search_form', 'placeholder' => 'Search']) !!}
                </div>
            </div>

            @if(!empty(session('upload_data')))
            <div class="row">
                <div class="col-12">
                    <livewire:sales.upload-progress :upload_data="session('upload_data')"/>
                </div>
            </div>
            @endif

            <b>{{$sales_uploads->total()}} total result{{$sales_uploads->total() > 1 ? 's' : ''}}</b>

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>CREATED AT</th>
                            <th>USER</th>
                            <th>COUNT</th>
                            <th>AMOUNT</th>
                            <th>CM AMOUNT</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales_uploads as $sale)
                        <tr>
                            <td class="align-middle">{{date('Y-m-d H:i:s a', strtotime($sale->created_at))}}</td>
                            <td class="align-middle text-nowrap">
                                <img class="img-circle elevation-2 mr-1" src="{{asset(!empty($sale->user->profile_picture_url) ? $sale->user->profile_picture_url.'-small.jpg': '/images/Windows_10_Default_Profile_Picture.svg')}}" alt="User Avatar" width="25px" height="25px">
                                {{$sale->user->name ?? '-'}}
                            </td>
                            <td class="align-middle">{{number_format($sale->sku_count) ?? 0}}</td>
                            <td class="align-middle">{{number_format($sale->total_amount_vat, 2) ?? 0}}</td>
                            <td class="align-middle">{{number_format($sale->total_cm_amount_vat, 2) ?? 0}}</td>
                            <td class="align-middle text-center text-nowrap">
                                @if(empty($sale->deleted_at))
                                    <a href="{{route('sales.show', encrypt($sale->id))}}" class="btn btn-info btn-xs" title="View details">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    <a href="{{route('sales.export', encrypt($sale->id))}}" class="btn btn-primary btn-xs" title="Export">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    @can('sales edit')
                                        <a href="{{route('sales.edit', encrypt($sale->id))}}" class="btn btn-success btn-xs" title="Edit">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('sales delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($sale->id)}}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                @else
                                    @can('sales restore')
                                        <a href="{{route('sales.restore', encrypt($sale->id))}}" class="btn btn-warning btn-xs" title="Restore">
                                            <i class="fa fa-recycle"></i>
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$sales_uploads->links(data: ['scrollTo' => false])}}
        </div>
    </div>

    @can('sales delete')
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
    @can('sales delete')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', { type: 'SalesUpload', model_id: id });
                $('#modal-delete').modal('show');
            });
        });
    </script>
    @endcan
@stop

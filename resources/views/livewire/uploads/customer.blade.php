<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Customer Upload</h4>
        </div>
        <div class="modal-body">

            @if(!empty($err_msg))
            <div class="alert alert-danger">
                {{$err_msg}}
            </div>
            @endif

            <div class="row">
                {{-- FILE --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        {!! Form::label('file', 'Upload File') !!}
                        {!! Form::file('file', ['class' => 'form-control'.($errors->has('file') ? ' is-invalid' : ''), 'wire:model.lazy' => 'file', 'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']) !!}
                        <small class="text-danger">{{$errors->first('file')}}</small>
                        @if(!empty($err_msg))
                            <small class="text-danger">{{$err_msg}}</small>
                        @endif
                    </div>
                </div>

                 {{-- COLUMN DESCRIPTIONS --}}
                 <div class="col-12">
                    <ul>
                        <li>
                            <b>CODE</b> - <span>Required, First column in the excel</span>
                        </li>
                        <li>
                            <b>NAME</b> - <span>Required, Second column in the excel</span>
                        </li>
                        <li>
                            <b>ADDRESS</b> - <span>Required, Third column in the excel</span>
                        </li>
                        <li>
                            <b>SALESMAN CODE</b> - <span>Optional, Fourth column in the excel</span>
                        </li>
                        <li>
                            <b>CHANNEL CODE</b> - <span>Required, Fifth column in the excel</span>
                        </li>
                        <li>
                            <b>CHANNEL NAME</b> - <span>Optional, Sixth column in the excel</span>
                        </li>
                    </ul>

                    <p>
                        <a href="{{asset('/templates/customer-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading customer data.
                    </p>
                </div>

                @if(empty($customer_data))
                <div class="col-12" wire:loading>
                    <label><i class="fa fa-spinner fa-spin mr-1"></i></span>Loading</label>
                </div>
                @endif

                {{-- PREVIEW --}}
                @if(!empty($customer_data))
                <div class="col-12">
                    <label>PREVIEW <span wire:loading><i class="fa fa-spinner fa-spin mr-1"></i></span></label>
                    <label class="float-right">COUNT: {{count($customer_data)}}</label>
                </div>
                <div class="col-12 table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th></th>
                                <th>CODE</th>
                                <th>NAME</th>
                                <th>ADDRESS</th>
                                <th>SALESMAN</th>
                                <th>CHANNEL</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginatedData as $key => $data)
                            <tr>
                                <td class="text-center align-middle">
                                    @switch($data['check'])
                                        @case(0)
                                            <i class="fa fa-check-circle text-success"></i>
                                        @break
                                        @case(1)
                                            <i class="fa fa-times-circle text-danger d-block"></i>
                                            <small class="text-danger">exists</small>
                                        @break
                                        @case(2)
                                            <i class="fa fa-times-circle text-danger d-block"></i>
                                            <small class="text-danger">Channel</small>
                                        @break
                                    @endswitch
                                </td>
                                <td class="align-middle">{{$data['code'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['name'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['address'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['salesman'] ?? '-'}}</td>
                                <td class="align-middle">{{!empty($data['channel']) ? '['.$data['channel']['code'].'] '.$data['channel']['name'] : '-'}}</td>
                                <td>
                                    @if(!empty($data['similar']) && $data['check'] == 0)
                                        @php
                                        $ubo = $data['similar'];
                                        @endphp
                                        <small class="text-warning font-weight-bold">This customer may already exist. Please review the details below. This customer will be automatically designated as a parked customer.</small>
                                        <br>
                                        <b>UBO ID: </b>{{$ubo['ubo_id']}} <b>NAME: </b>{{$ubo['name']}} <b>ADDRESS: </b>{{$ubo['address']}}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="col-12">
                    {{$paginatedData->links()}}
                </div>
                @endif

            </div>
            
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            @if(!empty($customer_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">Upload</button>
            @endif
        </div>
    </div>
</div>

<div style="position:relative;">

    @if($mode === 'modal')
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Customer Upload</h4>
        </div>
        <div class="modal-body">
    @else
    <div class="card card-default mb-0">
        <div class="card-header">
            <h3 class="card-title">CUSTOMER UPLOAD</h3>
        </div>
        <div class="card-body">
    @endif

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
                        {!! Form::file('file', ['class' => 'form-control'.($errors->has('file') ? ' is-invalid' : ''), 'wire:model.blur' => 'file', 'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']) !!}
                        <small class="text-danger">{{$errors->first('file')}}</small>
                        @if(!empty($err_msg))
                            <small class="text-danger">{{$err_msg}}</small>
                        @endif
                    </div>
                </div>

                 {{-- COLUMN DESCRIPTIONS --}}
                 <div class="col-12">
                    <ul>
                        <li><b>CODE</b> - <span>Required</span></li>
                        <li><b>NAME</b> - <span>Required</span></li>
                        <li><b>ADDRESS</b> - <span>Optional</span></li>
                        <li><b>SALESMAN CODE</b> - <span>Optional</span></li>
                        <li><b>CHANNEL CODE</b> - <span>Required</span></li>
                        <li><b>CHANNEL NAME</b> - <span>Optional</span></li>
                        <li><b>PROVINCE</b> - <span>Required</span></li>
                        <li><b>CITY/TOWN</b> - <span>Required</span></li>
                        <li><b>BARANGAY</b> - <span>Required</span></li>
                        <li><b>STREET</b> - <span>Optional</span></li>
                        <li><b>POSTAL CODE</b> - <span>Optional</span></li>
                    </ul>

                    <p>
                        <a href="{{asset('/templates/customer-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading customer data.
                    </p>
                </div>

                @if(empty($customer_data))
                <div class="col-12" wire:loading>
                    <label><i class="fa fa-spinner fa-spin mr-1"></i>Loading</label>
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
                                <th>STREET</th>
                                <th>BRGY</th>
                                <th>CITY</th>
                                <th>PROVINCE</th>
                                <th>POSTAL CODE</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginatedData as $key => $data)
                                @if(isset($data['check']) && !empty(strlen($data['check'])))
                                    <tr>
                                        <td class="text-center align-middle">
                                            @switch($data['check'])
                                                @case(0)
                                                    <i class="fa fa-check-circle text-success"></i>
                                                @break
                                                @case(1)
                                                    <i class="fa fa-times-circle text-danger d-block"></i>
                                                    <small class="text-danger">Exists</small>
                                                @break
                                                @case(2)
                                                    <i class="fa fa-times-circle text-danger d-block"></i>
                                                    <small class="text-danger">Invalid channel</small>
                                                    @if(!empty($data['invalid_channel_code']))
                                                        <small class="text-danger d-block">'{{$data['invalid_channel_code']}}'</small>
                                                    @endif
                                                @break
                                                @case(3)
                                                    <i class="fa fa-times-circle text-danger d-block"></i>
                                                    <small class="text-danger">Empty fields</small>
                                                    @foreach($data['empty_fields'] ?? [] as $field)
                                                        <small class="text-danger d-block">{{ $field }}</small>
                                                    @endforeach
                                                @break
                                            @endswitch
                                        </td>
                                        <td class="align-middle">{{$data['code'] ?? '-'}}</td>
                                        <td class="align-middle">{{$data['name'] ?? '-'}}</td>
                                        <td class="align-middle">{{$data['address'] ?? '-'}}</td>
                                        <td class="align-middle">{{$data['salesman'] ?? '-'}}</td>
                                        <td class="align-middle">
                                            @if(!empty($data['channel']))
                                                [{{$data['channel']['code']}}] {{$data['channel']['name']}}
                                            @elseif(!empty($data['invalid_channel_code']))
                                                <span class="text-danger">'{{$data['invalid_channel_code']}}' not found</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="align-middle">{{$data['street'] ?? '-'}}</td>
                                        <td class="align-middle">
                                            {{$data['brgy'] ?? '-'}}
                                            @if(!empty($data['brgy_id']))
                                                <i class="fa fa-check-circle text-success"></i>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            {{$data['city'] ?? '-'}}
                                            @if(!empty($data['city_id']))
                                                <i class="fa fa-check-circle text-success"></i>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            {{$data['province'] ?? '-'}}
                                            @if(!empty($data['province_id']))
                                                <i class="fa fa-check-circle text-success"></i>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{$data['postal_code'] ?? '-'}}</td>
                                        <td>
                                            @if(!empty($data['similar']) && $data['check'] == 0)
                                                @php $ubo = $data['similar']; @endphp
                                                <small class="text-warning font-weight-bold">This customer may already exist. Please review the details below. This customer will be automatically designated as a parked customer.</small>
                                                <br>
                                                <b>UBO ID: </b>{{$ubo['ubo_id']}} <b>NAME: </b>{{$ubo['name']}} <b>ADDRESS: </b>{{$ubo['address']}}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="col-12">
                    {{$paginatedData->links(data: ['scrollTo' => false])}}
                </div>
                @endif

            </div>

        </div>

        @if($mode === 'modal')
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            @if(!empty($customer_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">
                    <span wire:loading wire:target="uploadData"><i class="fa fa-spinner fa-spin mr-1"></i></span>
                    Upload
                </button>
            @endif
        </div>
        @else
        <div class="card-footer">
            @if(!empty($customer_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">
                    <span wire:loading wire:target="uploadData"><i class="fa fa-spinner fa-spin mr-1"></i></span>
                    Upload
                </button>
            @endif
        </div>
        @endif

    </div>
</div>

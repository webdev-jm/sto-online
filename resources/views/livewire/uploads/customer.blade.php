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
                        {!! Form::file('file', ['class' => 'form-control'.($errors->has('file') ? ' is-invalid' : ''), 'wire:model' => 'file', 'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']) !!}
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
                    </ul>

                    <p>
                        <a href="{{asset('/templates/customer-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading customer data.
                    </p>
                </div>

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
                                <th>CODE</th>
                                <th>NAME</th>
                                <th>ADDRESS</th>
                                <th>SALESMAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginatedData as $data)
                            <tr>
                                <td>{{$data['code'] ?? '-'}}</td>
                                <td>{{$data['name'] ?? '-'}}</td>
                                <td>{{$data['address'] ?? '-'}}</td>
                                <td>{{$data['salesman'] ?? '-'}}</td>
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

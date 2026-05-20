<div style="position:relative;">

    {{-- LOADING OVERLAY --}}
    <div wire:loading wire:target="file"
         class="d-flex align-items-center justify-content-center"
         style="position:absolute;top:0;left:0;width:100%;height:100%;
                background:rgba(255,255,255,0.85);z-index:9999;">
        <div class="text-center">
            <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2 d-block"></i>
            <strong>Processing file, please wait...</strong>
        </div>
    </div>

    @if($mode === 'modal')
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Location Upload</h4>
        </div>
        <div class="modal-body">
    @else
    <div class="card card-default mb-0">
        <div class="card-header">
            <h3 class="card-title">LOCATION UPLOAD</h3>
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
                        <li>
                            <b>CODE</b> - <span>Required, First column in the excel</span>
                        </li>
                        <li>
                            <b>NAME</b> - <span>Required, Second column in the excel</span>
                        </li>
                    </ul>

                    <p>
                        <a href="{{asset('/templates/location-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading location data.
                    </p>
                </div>

                {{-- PREVIEW --}}
                @if(!empty($location_data))
                <div class="col-12">
                    <label>PREVIEW <span wire:loading><i class="fa fa-spinner fa-spin mr-1"></i></span></label>
                    <label class="float-right">COUNT: {{count($location_data)}}</label>
                </div>
                <div class="col-12 table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th></th>
                                <th>CODE</th>
                                <th>NAME</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginatedData as $data)
                            <tr>
                                <td class="text-center align-middle">
                                    @if($data['check'] == 0)
                                        <i class="fa fa-check-circle text-success fa-sm"></i>
                                    @else
                                        <i class="fa fa-times-circle text-danger fa-sm"></i>
                                        <small class="text-danger">Duplicate</small>
                                    @endif
                                </td>
                                <td>{{$data['code'] ?? '-'}}</td>
                                <td>{{$data['name'] ?? '-'}}</td>
                            </tr>
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
            @if(!empty($location_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">
                    <span wire:loading wire:target="uploadData"><i class="fa fa-spinner fa-spin mr-1"></i></span>
                    Upload
                </button>
            @endif
        </div>
        @else
        <div class="card-footer">
            @if(!empty($location_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">
                    <span wire:loading wire:target="uploadData"><i class="fa fa-spinner fa-spin mr-1"></i></span>
                    Upload
                </button>
            @endif
        </div>
        @endif

    </div>

    @if($mode === 'modal')
    <script>
        document.addEventListener('livewire:load', function() {
            window.addEventListener('closeModal', e => {
                $('#modal-upload').modal('hide');
            });
        });
    </script>
    @endif
</div>

<div style="position:relative;">

    <div class="card card-default mb-0">
        <div class="card-header">
            <h3 class="card-title">DISTRICT UPLOAD</h3>
        </div>
        <div class="card-body">

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
                            <b>DISTRICT_CODE</b> - <span>Required, First column in the excel</span>
                        </li>
                        <li>
                            <b>AREA_CODES</b> - <span>Optional, Second column. Comma-separated area codes to assign to this district (e.g. <code>AREA1,AREA2,AREA3</code>)</span>
                        </li>
                    </ul>

                    <p>
                        <a href="{{asset('/templates/district-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading district data.
                    </p>
                </div>

                {{-- PREVIEW --}}
                @if(!empty($district_data))
                <div class="col-12">
                    <label>PREVIEW <span wire:loading><i class="fa fa-spinner fa-spin mr-1"></i></span></label>
                    <label class="float-right">COUNT: {{count($district_data)}}</label>
                </div>
                <div class="col-12 table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th></th>
                                <th>DISTRICT CODE</th>
                                <th>AREA CODES</th>
                                <th>INVALID AREAS</th>
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
                                <td>{{$data['district_code'] ?? '-'}}</td>
                                <td>
                                    @if(!empty($data['area_codes']))
                                        <span>{{$data['area_codes']}}</span>
                                        @if(!empty($data['area_ids']))
                                            <span class="badge badge-success ml-1">{{count($data['area_ids'])}} matched</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($data['invalid_areas']))
                                        @foreach($data['invalid_areas'] as $invalid)
                                            <span class="badge badge-danger mr-1">{{$invalid}}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
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
        <div class="card-footer">
            @if(!empty($district_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">
                    <span wire:loading wire:target="uploadData"><i class="fa fa-spinner fa-spin mr-1"></i></span>
                    Upload
                </button>
            @endif
        </div>
    </div>
</div>

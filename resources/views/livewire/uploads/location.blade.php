<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Location Upload</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                {{-- FILE --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        {!! Form::label('file', 'Upload File') !!}
                        {!! Form::file('file', ['class' => 'form-control'.($errors->has('file') ? ' is-invalid' : ''), 'wire:model' => 'file', 'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']) !!}
                        <small class="text-danger">{{$errors->first('file')}}</small>
                    </div>
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
                                <th>CODE</th>
                                <th>NAME</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginatedData as $data)
                            <tr>
                                <td>{{$data['code'] ?? '-'}}</td>
                                <td>{{$data['name'] ?? '-'}}</td>
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
            @if(!empty($location_data))
                <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">Upload</button>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            window.addEventListener('closeModal', e => {
                $('#modal-upload').modal('hide');
            });
        });
    </script>
</div>

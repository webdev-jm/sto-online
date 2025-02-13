<div>
<div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Purchase Order Upload</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="" class="mb-0">UPLOAD FILE</label>
                        <input type="file" class="form-control form-control-sm{{$errors->has('file') ? ' is-invalid' : ''}}" wire:model.live="file">
                        <small class="text-danger">{{$errors->first('file')}}</small>
                    </div>
                </div>
                
                <div class="col-lg-12">
                    <button class="btn btn-info btn-sm" wire:click.prevent="checkUploads">CHECK FILE</button>
                </div>
            </div>

            @if(!empty($po_data))
            <hr>
            <div class="row mt-2">
                <div class="col-lg-6">
                    <ul class="list-group">
                        @foreach($po_data['header'] as $name => $val)
                            <li class="list-group-item py-1">
                                <b>{{$name}}:</b> {{$val}}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-lg-12 table-responsive mt-2">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr class="bg-gray text-center">
                                <th>BEVI SKU CODE</th>
                                <th>OTHER SKU CODE</th>
                                <th>UOM</th>
                                <th>QUANTITY</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po_data['details'] as $key => $detail)
                                <tr>
                                    <td>{{$detail['BEVI SKU CODE']}}</td>
                                    <td>{{$detail['OTHER SKU CODE']}}</td>
                                    <td>{{$detail['UOM']}}</td>
                                    <td>{{$detail['QUANTITY']}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" wire:click.prevent="uploadData" wire:loading.attr="disabled">Upload</button>
        </div>
    </div>
</div>

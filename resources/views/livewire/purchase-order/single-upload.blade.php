<div>
<div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Purchase Order Upload</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group mb-0">
                        <label for="" class="mb-0">UPLOAD FILE</label>
                        <input type="file" class="form-control form-control-sm{{$errors->has('file') ? ' is-invalid' : ''}}" wire:model.live="file">
                        <small class="text-danger">{{$errors->first('file')}}</small>
                    </div>
                </div>

                <div class="col-12">
                    <a href="{{asset('/templates/po-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading purchase order data.
                </div>
                
                <div class="col-lg-12 mt-2">
                    <button class="btn btn-info btn-xs" wire:click.prevent="checkUploads" wire:loading.attr="disabled" wire:target="checkUploads">CHECK FILE</button>
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
            @if(!empty($po_data))
                <button type="button" class="btn btn-primary" wire:loading.attr="disabled" wire:click.prevent="uploadData">Upload</button>
            @endif
        </div>
    </div>
</div>

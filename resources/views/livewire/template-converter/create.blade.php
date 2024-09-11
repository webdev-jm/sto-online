<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TEMPLATE CONVERTER</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">UPLOAD FILE</label>
                        <input type="file" class="form-control{{$errors->has('file_upload') ? ' is-invalid' : ''}}" wire:model="file_upload">
                        <small class="text-danger">{{$errors->first('file_upload')}}</small>
                    </div>
                </div>
            </div>

            <i class="fa fa-spinner fa-spin" wire:loading></i>

        </div>
        <div class="card-footer">
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DATA</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $val)
                        <tr>
                            <td>{{$val['date'] ?? ''}}</td>
                            <td>{{$val['type'] ?? ''}}</td>
                            <td>{{$val['branch'] ?? ''}}</td>
                            <td>{{$val['customer'] ?? ''}}</td>
                            <td>{{$val['sku'] ?? ''}}</td>
                            <td>{{$val['stock_code'] ?? ''}}</td>
                            <td>{{$val['description'] ?? ''}}</td>
                            <td>{{$val['qty'] ?? ''}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

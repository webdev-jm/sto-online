<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">EDIT TEMPLATE</h3>
        </div>
        <div class="card-body">

            @if(!empty($success_msg))
            <div class="alert alert-success">
                {{$success_msg}}
            </div>
            @endif
        
            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">TEMPLATE</label>
                        <p class="text-lg">{{$accountTemplate->upload_template->title}}</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">START ROW</label>
                        <input type="number" class="form-control{{$errors->has('start_row') ? ' is-invalid' : ''}}" wire:model="start_row">
                        <small class="text-danger">{{$errors->first('start_row')}}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">COLUMN TYPE</label>
                        <select class="form-control{{$errors->has('column_type') ? ' is-invalid' : ''}}" wire:model="column_type">
                            <option value="" selected>- SELECT -</option>
                            <option value="name">COLUMN NAME</option>
                            <option value="number">COLUMN NUMBER</option>
                        </select>
                        <small class="text-danger">{{$errors->first('column_type')}}</small>
                    </div>
                </div>

            </div>

            <strong>FIELD MAPPING</strong>
            <hr class="mt-0 mb-2">

            @if($errors->has('account_template_fields'))
                {{var_dump($errors->get('account_template_fields'))}}
            @endif

            @if(!empty($template_fields))
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr class="text-center">
                                <th class="p-0 align-middle">#</th>
                                <th class="p-0 align-middle">FIELD</th>
                                <th class="p-0 align-middle">
                                    FILE COLUMN NAME
                                </th>
                                <th class="p-0 align-middle">
                                    FILE COLUMN NUMBER
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($template_fields as $field)
                            <tr class="{{!empty($err[$field->id]) ? 'text-red' : ''}}">
                                <th class="p-0 align-middle text-center">{{$field->number}}</th>
                                <th class="p-0 align-middle text-center">{{$field->column_name}}</th>
                                <td class="p-0">
                                    <input type="text" class="border-0 form-control" wire:model.defer="account_template_fields.{{$field->id}}.name">
                                </td>
                                <td class="p-0">
                                    <input type="number" class="border-0 form-control" wire:model.defer="account_template_fields.{{$field->id}}.number">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-success btn-sm" wire:click.prevent="update">
                <i class="fa fa-save mr-1"></i>
                UPDATE
            </button>
        </div>
    </div>
</div>

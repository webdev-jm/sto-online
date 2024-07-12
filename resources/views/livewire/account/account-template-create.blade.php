<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">CREATE TEMPLATE</h3>
        </div>
        <div class="card-body">
        
            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">TEMPLATE</label>
                        <select class="form-control{{$errors->has('template_id') ? ' is-invalid' : ''}}" wire:model="template_id">
                            <option value="" selected>- SELECT -</option>
                            @foreach($templates as $template)
                                <option value="{{$template->id}}">{{$template->title}}</option>
                            @endforeach
                        </select>
                        <small class="text-danger">{{$errors->first('template_id')}}</small>
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
                                <th class="p-0 align-middle">FILE COLUMN NAME</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($template_fields as $field)
                            <tr class="{{!empty($err[$field->id]) ? 'text-red' : ''}}">
                                <th class="p-0 align-middle text-center">{{$field->number}}</th>
                                <th class="p-0 align-middle text-center">{{$field->column_name}}</th>
                                <td class="p-0">
                                    <input type="text" class="border-0 form-control" wire:model="account_template_fields.{{$field->id}}.name">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="save">
                <i class="fa fa-save mr-1"></i>
                SAVE
            </button>
        </div>
    </div>
</div>

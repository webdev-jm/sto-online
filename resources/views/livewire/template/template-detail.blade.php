<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">TEMPLATE FIELDS</h3>
        </div>
        <div class="card-body p-1">
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-1">
                    <thead>
                        <tr class="text-center">
                            <th class="p-0 align-middle">#</th>
                            <th class="p-0 align-middle">Column</th>
                            <th class="p-0 align-middle">Alternative Column</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($lines))
                            @php
                                $num = 0;
                            @endphp
                            @foreach($lines as $key => $line)
                                @php
                                    $num++;
                                @endphp
                                <tr>
                                    <td class="p-0 align-middle text-center">
                                        {{$num}}
                                    </td>
                                    <td class="p-0">
                                        <input type="text" class="form-control border-0" wire:model.defer="lines.{{$key}}.column_name">
                                    </td>
                                    <td class="p-0">
                                        <input type="text" class="form-control border-0" wire:model.defer="lines.{{$key}}.column_name_alt">
                                    </td>
                                    <td class="p-0 text-center align-middle">
                                        <a href="" class="text-danger" wire:click.prevent="removeLine({{$key}})">
                                            <i class="fa fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="row mb-1">
                <div class="col-12">
                    <button class="btn btn-info btn-xs" wire:click.prevent="addLine" wire:loading.attr="disabled" wire:target="addLine">
                        <i class="fa fa-plus mr-1"></i>
                        ADD LINE
                    </button>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-xs" wire:click.prevent="saveDetail" wire:loading.attr="disabled">
                <i class="fa fa-save mr-1"></i>
                SAVE
            </button>
        </div>
    </div>
</div>

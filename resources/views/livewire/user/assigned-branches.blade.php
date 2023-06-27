<div>
    @if(!empty($form_message))
        <div class="alert alert-success">
            {{$form_message}}
        </div>
    @endif

    <div class="card mb-0">
        <div class="card-header">
            <h3 class="card-title">Branches</h3>
            <div class="card-tools">
                <input type="text" class="form-control form-control-sm" wire:model="search" placeholder="Search">
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3">
                    <button class="btn btn-secondary" wire:loading.attr="disabled" wire:click.prevent="clear">Clear</button>
                    <button class="btn btn-primary" wire:loading.attr="disabled" wire:click.prevent="selectAll">Select All</button>
                </div>
            </div>

            <div class="row">
                @foreach($branches as $branch)
                    @php
                        $class = 'btn-default';
                        if(!empty($selected) && in_array($branch->id, $selected)) {
                            $class = 'btn-primary';
                        }
                    @endphp
                    <div class="col-lg-6 mb-1">
                        <button class="btn btn-block {{$class}}" wire:loading.attr="disabled" wire:click.prevent="selectBranch('{{encrypt($branch->id)}}')">[{{$branch->code}}] {{$branch->name}}</button>
                    </div>
                @endforeach
            </div>

            <div class="row mt-1">
                <div class="col-12">
                    {{$branches->links()}}
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary" wire:loading.attr="disabled" wire:click.prevent="assign">Assign</button>
        </div>
    </div>
</div>

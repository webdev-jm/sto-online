<div>
    @if(!empty($account_id))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">BRANCHES</h3>
            <div class="card-tools">
                <input type="text" class="form-control form-control-sm" placeholder="Search" wire:model.live="search">
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                @foreach($branches as $branch)
                <div class="col-lg-3">
                    <button class="btn {{$branch_id == $branch->id ? 'btn-success' : 'btn-default'}} btn-block" wire:click.prevent="selectBranch('{{encrypt($branch->id)}}')">
                        {{$branch->code}} {{$branch->name}}
                    </button>
                </div>
                @endforeach

                <div class="col-12 mt-2">
                    {{$branches->links()}}
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary" wire:click="runJob">
                <i class="fa fa-clock mr-1"></i>
                RUN JOB
            </button>
        </div>
    </div>
    @endif
</div>

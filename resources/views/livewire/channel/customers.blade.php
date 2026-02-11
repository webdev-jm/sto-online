<div>
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">CUSTOMERS</h3>
            <div class="card-tools">
                <input type="text" class="form-control form-control-sm" placeholder="Search" wire:model.live="search">
            </div>
        </div>
        <div class="card-body p-0 table-responsive">

            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>CODE</th>
                        <th>NAME</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($customers->total()))
                        @foreach($customers as $customer)
                        <tr>
                            <td>{{$customer->code}}</td>
                            <td>{{$customer->name}}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

        </div>
        <div class="card-footer">
            {{$customers->links()}}
        </div>
    </div>
</div>

<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">CUSTOMERS</h3>
            <div class="card-tools">
                <b>COUNT: {{$customers->total()}}</b>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>CODE</th>
                        <th>NAME</th>
                        <th>ADDRESS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>{{$customer->code}}</td>
                        <td>{{$customer->name}}</td>
                        <td>{{$customer->address}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$customers->links()}}
        </div>
    </div>
</div>

<div>
    <form wire:submit.prevent="saveCustomer">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Customer</h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    
                    {{-- CODE --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="customer_code">Customer Code</label>
                            <input type="text" class="form-control{{$errors->has('customer_code') ? ' is-invalid' : ''}}" wire:model="customer_code">
                            @error('customer_code')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- NAME --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" class="form-control{{$errors->has('customer_name') ? ' is-invalid' : ''}}" wire:model="customer_name">
                            @error('customer_name')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- ADDRESS --}}
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="customer_address">Address</label>
                            <input type="text" class="form-control{{$errors->has('customer_address') ? ' is-invalid' : ''}}" wire:model="customer_address">
                            @error('customer_address')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- SALESMAN --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="salesman">Salesman</label>
                            <select id="salesman" class="form-control{{$errors->has('salesman_id') ? ' is-invalid' : ''}}" wire:model="salesman_id">
                                <option value="">- select salesman -</option>
                                @if(!empty($salesmen))
                                    @foreach($salesmen as $id => $salesman)
                                        <option value="{{$id}}">{{$salesman}}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('salesman_id')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="submit" class="btn btn-primary">Add Customer</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
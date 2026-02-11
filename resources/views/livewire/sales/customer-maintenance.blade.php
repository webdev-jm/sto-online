<div>
    <form wire:submit="saveCustomer">
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
                            <input type="text" class="form-control{{$errors->has('customer_code') ? ' is-invalid' : ''}}" wire:model.live="customer_code">
                            @error('customer_code')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- NAME --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" class="form-control{{$errors->has('customer_name') ? ' is-invalid' : ''}}" wire:model.live="customer_name">
                            @error('customer_name')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- SALESMAN --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="salesman">Salesman</label>
                            <select id="salesman" class="form-control{{$errors->has('salesman_id') ? ' is-invalid' : ''}}" wire:model.live="salesman_id">
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

                    {{-- CHANNEL --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="channel_id">Salesman</label>
                            <select id="channel_id" class="form-control{{$errors->has('channel_id') ? ' is-invalid' : ''}}" wire:model.live="channel_id">
                                <option value="">- select channels -</option>
                                @if(!empty($channels))
                                    @foreach($channels as $id => $channel)
                                        <option value="{{$id}}">{{$channel}}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('channel_id')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>
                    
                </div>

                <hr>
                
                <div class="row">
                    {{-- PROVINCE --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="province_id">Province</label>
                            <select id="province_id" class="form-control{{$errors->has('province_id') ? ' is-invalid' : ''}}" wire:model.live="province_id">
                                <option value="">- select province -</option>
                                @if(!empty($provinces))
                                    @foreach($provinces as $id => $province_name)
                                        <option value="{{$id}}">{{$province_name}}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('province_id')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>
                    {{-- CITY/TOWN --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">City/Town</label>
                            @if(empty($province_id))
                                <input type="text" class="form-control{{$errors->has('city_id') ? ' is-invalid' : ''}}" disabled>
                            @else
                                <select id="city_id" class="form-control{{$errors->has('city_id') ? ' is-invalid' : ''}}" wire:model.live="city_id">
                                    <option value="">- select city -</option>
                                    @if(!empty($cities))
                                        @foreach($cities as $id => $city_name)
                                            <option value="{{$id}}">{{$city_name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @endif
                            @error('city_id')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>
                    {{-- BARANGAY --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Barangay</label>
                            @if(empty($city_id))
                                <input type="text" class="form-control{{$errors->has('barangay_id') ? ' is-invalid' : ''}}" disabled>
                            @else
                                <select id="barangay_id" class="form-control{{$errors->has('barangay_id') ? ' is-invalid' : ''}}" wire:model.live="barangay_id">
                                    <option value="">- select barangay -</option>
                                    @if(!empty($barangays))
                                        @foreach($barangays as $id => $barangay_name)
                                            <option value="{{$id}}">{{$barangay_name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @endif
                            @error('barangay_id')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>
                    {{-- STREET --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Street</label>
                            <input type="text" class="form-control{{$errors->has('street') ? ' is-invalid' : ''}}" wire:model.live="street">
                            @error('street')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>
                    {{-- ADDRESS --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="customer_address">Address</label>
                            <input type="text" class="form-control{{$errors->has('customer_address') ? ' is-invalid' : ''}}" wire:model.live="customer_address">
                            @error('customer_address')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>
                    {{-- POSTAL CODE --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" class="form-control{{$errors->has('postal_code') ? ' is-invalid' : ''}}" wire:model.live="postal_code">
                            @error('postal_code')
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
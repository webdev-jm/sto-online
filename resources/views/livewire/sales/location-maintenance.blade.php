<div>
    <form wire:submit="saveLocation">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Location</h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    
                    {{-- CODE --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="location_code">Location Code</label>
                            <input type="text" class="form-control{{$errors->has('location_code') ? ' is-invalid' : ''}}" wire:model.live="location_code">
                            @error('location_code')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- NAME --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="location_name">Location Name</label>
                            <input type="text" class="form-control{{$errors->has('location_name') ? ' is-invalid' : ''}}" wire:model.live="location_name">
                            @error('location_name')
                                <small class="text-danger">{{$message}}</small>
                            @enderror
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="submit" class="btn btn-primary">Add Location</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
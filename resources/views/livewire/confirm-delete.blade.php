<div>
    <form wire:submit.prevent="submitForm" autocomplete="off">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure to delete this data? <br>
                </p>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="password">Please enter password to continue.</label>
                            <input type="password" class="form-control" wire:model.lazy="password" placeholder="Password">
                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                            <p class="text-danger">{{$error_message}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Delete</button>
            </div>
        </div>
    </form>
</div>

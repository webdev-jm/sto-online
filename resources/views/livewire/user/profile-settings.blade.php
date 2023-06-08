<div>
    <form wire:submit.prevent="saveSettings">
        <div class="row">
            
            <div class="col-lg-6">
                <div class="widget-user-image text-center">
                    @if($profile_picture)
                        <img class="img-circle elevation-2" src="{{$profile_picture->temporaryUrl()}}" alt="User Avatar" width="60px" height="60px">
                    @else
                        <img class="img-circle elevation-2" src="{{!empty(auth()->user()->profile_picture_url) ? auth()->user()->profile_picture_url.'-small.jpg': '/images/avatar.png'}}" alt="User Avatar" width="60px" height="60px">
                    @endif
                </div>
                <div class="form-group mt-1">
                    <label>Profile Picture</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" wire:model="profile_picture">
                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                        </div>
                    </div>
                </div>
            </div>

            @can('user change signature')
            <div class="col-lg-6">
                <div class="widget-user-image text-center">
                    @if($user_signature)
                        <img class="" src="{{$user_signature->temporaryUrl()}}" alt="User Avatar" width="60px" height="60px">
                    @else
                        <img class="" src="{{!empty(auth()->user()->user_signature_url) ? auth()->user()->user_signature_url.'-large.jpg': '/images/avatar.png'}}" alt="User Avatar" height="60px">
                    @endif
                </div>
                <div class="form-group mt-1">
                    <label>Signature</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" wire:model="user_signature">
                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <div class="col-lg-6">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control{{$errors->has('name') ? ' is-invalid' : ''}}" wire:model.defer="name">
                    @error('name')
                        <p class="text-danger mt-1">{{$message}}</p>
                    @enderror
                </div>
            </div>

            <div class="col-lg-6">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control{{$errors->has('username') ? ' is-invalid' : ''}}" wire:model.defer="username">
                    @error('username')
                        <p class="text-danger mt-1">{{$message}}</p>
                    @enderror
                </div>
            </div>

        </div>

        <div class="row mt-2">
            <div class="col-12">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">SAVE</button>
            </div>
        </div>
    </form>
</div>

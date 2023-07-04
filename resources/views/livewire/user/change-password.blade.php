<div>
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="submitForm" autocomplete="off">
        <div class="form-group row">
            <label for="current" class="col-sm-3 col-form-label">Current Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Current Password" wire:model.lazy="current_password">
                @error('current_password')
                    <p class="text-danger">{{$message}}</p>
                @enderror
                @if(!empty($password_error))
                    <p class="text-danger">{{$password_error}}</p>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="new" class="col-sm-3 col-form-label">New Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="New Password" wire:model.lazy="password">
                @error('password')
                    <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="confirm" class="col-sm-3 col-form-label">Confirm Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Confirm Password" wire:model.lazy="password_confirmation">
                @error('password')
                    <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-12">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">CHANGE PASSWORD</button>
            </div>
        </div>
    </form>
</div>

<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">USERS</h3>
        </div>
        <div class="card-body py-0">
            <div class="row">
                @foreach($users as $user)
                <div class="col-lg-3 my-1">
                    <img class="img-circle elevation-2" src="{{asset(!empty($user->profile_picture_url) ? $user->profile_picture_url.'-small.jpg': '/images/Windows_10_Default_Profile_Picture.svg')}}" alt="User Avatar" width="30px" height="30px">
                    {{$user->name}}
                </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            {{$users->links()}}
        </div>
    </div>
</div>

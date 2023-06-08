<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithFileUploads;

use Intervention\Image\Facades\Image;

use Illuminate\Validation\Rule;

class ProfileSettings extends Component
{
    use WithFileUploads;

    public $name, $username, $profile_picture, $user_signature;

    public function saveSettings() {
        $this->validate([
            'name' => [
                'required',
                Rule::unique('users')->ignore(auth()->user()->id)
            ],
            'username' => [
                'required',
                Rule::unique('users')->ignore(auth()->user()->id)
            ]
        ]);

        // PROFILE PICTURE
        if(!empty($this->profile_picture)) {
            $profile_picture_url = $this->saveImage($this->profile_picture, 'profile');
        } else {
            $profile_picture_url = auth()->user()->profile_picture_url;
        }

        // SIGNATURE
        if(!empty($this->user_signature)) {
            $user_signature_url = $this->saveImage($this->user_signature, 'signature');
        } else {
            $user_signature_url = auth()->user()->user_signature_url;
        }

        auth()->user()->update([
            'name' => $this->name,
            'username' => $this->username,
            'profile_picture_url' => $profile_picture_url,
            'user_signature_url' => $user_signature_url
        ]);
        
    }

    public function saveImage($image_input, $file_name) {
        $dir = public_path().'/uploads/user-profile/'.auth()->user()->id;
        if(!is_dir($dir)) {
            mkdir($dir, 755, true);
        }

        $image = Image::make($image_input);
        if($image->width() > $image->height()) { // landscape
            $image->widen(800)->save($dir.'/'.$file_name.'-large.jpg'); // large
        } else { // portrait
            $image->heighten(700)->save($dir.'/'.$file_name.'-large.jpg'); // large
        }
        $image = Image::make($image_input);
        $image->fit(100, 100)->save($dir.'/'.$file_name.'-small.jpg'); // small

        return '/uploads/user-profile/'.auth()->user()->id.'/'.$file_name;
    }

    public function mount() {
        $user = auth()->user();
        $this->name = $user->name;
        $this->username = $user->username;
    }

    public function render()
    {
        return view('livewire.user.profile-settings');
    }
}

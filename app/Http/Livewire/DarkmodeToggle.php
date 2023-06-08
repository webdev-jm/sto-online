<?php

namespace App\Http\Livewire;

use Livewire\Component;

class DarkmodeToggle extends Component
{
    public function changeMode() {
        if(auth()->user()->dark_mode) {
            auth()->user()->update([
                'dark_mode' => 0
            ]);
        } else {
            auth()->user()->update([
                'dark_mode' => 1
            ]);
        }
    }

    public function render()
    {
        return view('livewire.darkmode-toggle');
    }
}


<li class="nav-item">
    <a class="nav-link" href="#" role="button" id="darkModeToggle" wire:click="changeMode">
        @if(!empty(auth()->user()) && auth()->user()->dark_mode)
            <i class="fas fa-moon"></i>
        @else
            <i class="fas fa-sun text-warning"></i>
        @endif
    </a>
</li>

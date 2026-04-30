<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Home extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.frontend.home');
    }
}

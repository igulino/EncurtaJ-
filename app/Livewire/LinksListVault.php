<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class LinksListVault extends Component
{
    #[On('link-created')]
    public function refreshList()
    {

    }

    public function render()
    {
        //$this->dispatch('link-saved', $param = true);
        return view('livewire.links-list-vault', [
            'vaults' => auth()->user()->userlinks()->latest()->get()
        ]);
    }
}

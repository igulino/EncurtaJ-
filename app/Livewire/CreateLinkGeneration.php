<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\LinkController;

class CreateLinkGeneration extends Component
{
    
    public ?string $Name = null;
    public ?string $Link = null;

    public function save()
    {
        Log::info('entrou em save-link');

        $this->validate([
            'Name' => 'required|string|max:255',
            'Link' => 'required|url|max:255',
        ]);

        try {
            Log::info('validation passed, calling LinkController::LinkCreation', [
                'Name' => $this->Name,
                'Link' => $this->Link
            ]);

            LinkController::LinkCreation($this->Name, $this->Link);

            Log::info('Pós linkController');

            $this->reset(['Name', 'Link']);
            $this->dispatch('link-created');
            $this->dispatch('close-modal');

            Log::info('Pós dispatch');

        } catch (\Throwable $e) {
            Log::error('link creation failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.create-link-generation');
    }
}
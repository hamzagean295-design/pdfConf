<?php

use Livewire\Component;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    public Document $document;
    public string $name;
    public function mount()
    {
        $this->name = $this->document->name;
    }

    public $firstClick = 0;
    public $showIt = false;

    public function doubleClick()
    {

        if ($this->firstClick == 0) { // first click
            $this->firstClick = microtime(true);
        } else {
            $interval = microtime(true) - $this->firstClick;
            if ($interval <= 0.5) { // 500ms interval
                $this->showIt = !$this->showIt;
            } else {
                $this->firstClick = 0;
                $this->doubleClick();
            }
        }
    }
    public function save()
    {
        $this->document->update([
            'name' => $this->name
        ]);
        $this->showIt = false;
        $this->firstClick = 0;
    }

};
?>

<div>
      <h1 class="text-center pb-2 text-xl font-bold">
        Configurer votre modèle
        @if($showIt)
            <form wire:submit="save" class="inline">
                <input type="text" wire:model="name" class="text-center">
                <input type="submit" value="sauvegarder" class="text-sm border font-normal p-2 border-b">
            </form>
        @else
            <span class="border p-1 border-2 border-black border-b" wire:click="doubleClick"> {{ $document->name }} </span>
        @endif
    </h1>
</div>

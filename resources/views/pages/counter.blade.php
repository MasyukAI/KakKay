<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.pages')]

class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
};

?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">INCREMENT</button>
</div>

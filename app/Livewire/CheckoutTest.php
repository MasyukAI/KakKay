<?php

namespace App\Livewire;

use Livewire\Component;

class CheckoutTest extends Component
{
    public string $message = 'Hello World!';

    public function test()
    {
        $this->message = 'Button clicked!';
    }

    public function render()
    {
        return view('livewire.checkout-test');
    }
}

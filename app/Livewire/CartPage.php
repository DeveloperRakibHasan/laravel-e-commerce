<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cart - Tech Store')]
class CartPage extends Component
{
    public $cart_items = [];
    public $grand_total;

    public function mount()
    {
        $this->cart_items = CartManagement::getCartItemsFromCookie();
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }

    public function removeItem($product_id)
    {
       $this->cart_items = CartManagement::removeCartItems($product_id);
       $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }
    public function render()
    {
        return view('livewire.cart-page');
    }
}

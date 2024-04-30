<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;

#[Title('Products - Tech Store')]
class ProductsPage extends Component
{
    use WithPagination;
    public function render()
    {
        $productQuery = Product::query()->where('is_active', 1);
//        dd($productQuery);
        return view('livewire.products-page', [
            'products' => $productQuery->paginate(6)
        ]);
    }
}

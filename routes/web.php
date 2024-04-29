<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage;
use App\Livewire\CategoriesPage;

Route::get('/', HomePage::class);
Route::get('/categories', CategoriesPage::class);

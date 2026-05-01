<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\OrderShow;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/orders/{order}', OrderShow::class)->name('orders.show');

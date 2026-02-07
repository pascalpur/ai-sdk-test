<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Product Categories Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('product-categories', 'pages::product-categories.index')
        ->name('product-categories.index');
    Route::livewire('product-categories/create', 'pages::product-categories.form')
        ->name('product-categories.create');
    Route::livewire('product-categories/{id}/edit', 'pages::product-categories.form')
        ->name('product-categories.edit');
});

// Products Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('products', 'pages::products.index')
        ->name('products.index');
    Route::livewire('products/create', 'pages::products.form')
        ->name('products.create');
    Route::livewire('products/{id}/edit', 'pages::products.form')
        ->name('products.edit');
});

require __DIR__ . '/settings.php';

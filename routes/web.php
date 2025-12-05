<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/properties/create', function () {
    return view('properties.create');
});

Route::get('/my-properties', function () {
    return view('properties.my-properties');
});

Route::get('/properties/{id}', function ($id) {
    return view('properties.show', ['id' => $id]);
});

Route::get('/favorites', function () {
    return view('properties.favorites');
});

Route::get('/admin', function () {
    return view('admin.dashboard');
})->middleware('auth:sanctum');


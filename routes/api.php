<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeolocationController;

// Get location by IP address
Route::get('/location/by-ip', [GeolocationController::class, 'getLocationByIp'])
    ->name('api.location.by-ip');

// Get coordinates by address (geocoding)
Route::get('/location/by-address', [GeolocationController::class, 'getCoordinatesByAddress'])
    ->name('api.location.by-address');

// Get address by coordinates (reverse geocoding)
Route::get('/location/by-coordinates', [GeolocationController::class, 'getAddressByCoordinates'])
    ->name('api.location.by-coordinates');

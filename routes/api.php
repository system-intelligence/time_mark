<?php

use App\Http\Controllers\GeolocationController;
use Illuminate\Support\Facades\Route;

Route::get('/location/by-ip', [GeolocationController::class, 'getLocationByIp'])
    ->name('api.location.by-ip');

Route::get('/location/by-address', [GeolocationController::class, 'getCoordinatesByAddress'])
    ->name('api.location.by-address');

Route::get('/location/by-coordinates', [GeolocationController::class, 'getAddressByCoordinates'])
    ->name('api.location.by-coordinates');

Route::post('/connection/detect', [GeolocationController::class, 'detectConnectionType'])
    ->name('api.connection.detect');

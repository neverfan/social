<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

//получение данных пользователя по id
Route::get('/user/get/{user_id}', [UserController::class, 'get'])
    ->name('user.get');

//получение данных пользователя по id
Route::post('/user/register', [UserController::class, 'register'])
    ->name('user.register');

//авторизация
Route::post('/user/login', [UserController::class, 'login'])
    ->name('user.login');

//получение данных текущего пользователя (требуется авторизация)
Route::get('/user/current', [UserController::class, 'current'])
    ->middleware(Authenticate::class)
    ->name('user.current');

//обновить токен текущего пользователя (требуется авторизация)
Route::get('/user/refresh', [UserController::class, 'refresh'])
    ->middleware(Authenticate::class)
    ->name('user.refresh');

//поиск анкет
Route::get('/user/search', [UserController::class, 'search'])
    ->name('user.search');

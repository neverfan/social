<?php

use App\Http\Controllers\FriendController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/**
 * User
 */
Route::group([
    'prefix' => '/user',
    'controller' => UserController::class,
    'as' => 'user'
], function () {
    //получение данных пользователя по id
    Route::get('/get/{user}', 'get')->name('.get');

    //получение данных пользователя по id
    Route::post('/register', 'register')->name('.register');

    //авторизация
    Route::post('/login', 'login')->name('.login');

    //получение данных текущего пользователя (требуется авторизация)
    Route::get('/current', [UserController::class, 'current'])
        ->middleware('auth')
        ->name('.current');

    //обновить токен текущего пользователя (требуется авторизация)
    Route::get('/refresh', 'refresh')
        ->middleware('auth')
        ->name('.refresh');

    //поиск анкет
    Route::get('/search', 'search')->name('.search');
});

/**
 * Friend
 */
Route::group([
    'prefix' => '/friend',
    'controller' => FriendController::class,
    'as' => 'friend',
    'middleware' => 'auth'
], function () {
    //Добавить друга
    Route::put('/set/{friend}', 'set')->name('.set');

    //Удалить друга
    Route::put('/delete/{friend}', 'delete')->name('.delete');
});

/**
 * Post
 */
Route::group([
    'prefix' => '/post',
    'controller' => \App\Http\Controllers\PostController::class,
    'as' => 'post',
], function () {
    //Получить пост
    Route::get('/get/{post}', 'get')->name('.get');

    //Требуется авторизация
    Route::group(['middleware' => 'auth'], function () {
        //Создать пост
        Route::post('/create', 'create')->name('.create');

        //Обновить пост
        Route::put('/update/{post}', 'update')->name('.update');

        //Удалить пост
        Route::put('/delete/{post}', 'delete')->name('.delete');

        //Лента постов друзей
        Route::get('/feed', 'feed')->name('.feed');
    });
});












<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});



//Rutas controlador usuario

Route::post('api/usuarios/login','UserController@login');
Route::put('api/usuarios/update','UserController@update');
Route::post('api/usuarios/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::post('api/usuarios/register','UserController@register');
Route::get('api/usuarios/avatar/{filename}','UserController@get_avatar');
Route::get('api/usuarios/detalle/{id}','UserController@details');

//Rutas controlador categorias
Route::resource('api/categorias', 'CateogryController');
Route::get('api/categorias/{id}', 'CateogryController@show');
Route::post('api/categorias', 'CateogryController@store');
Route::put('api/categorias/{id}', 'CateogryController@update');

//Rutas controlador post
Route::resource('api/post', 'PostController');
Route::post('api/post', 'PostController@store');
Route::post('api/post/upload','PostController@upload');
Route::get('api/post/image/{filename}','PostController@getImage');
Route::get('api/post/category/{id}','PostController@getPostByCategory');
Route::get('api/post/user/{id}','PostController@getPostByUser');
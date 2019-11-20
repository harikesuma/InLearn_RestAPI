<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');

Route::group(['middleware' => 'auth:api'], function(){
	Route::post('details', 'API\UserController@details');

	/**
	 * Route Pertanyaan
	 */
	Route::post('showPertanyaan','API\PertanyaanController@showPertanyaan'); //Route menampilkan seluruh pertanyaan pada Home App
	Route::post('historyActivity','API\PertanyaanController@historyQuestion'); //Route menampilkan seluruh history pertanyaan dan jawaban user
	Route::post('postPertanyaan','API\PertanyaanController@postPertanyaan'); //Route post Pertanyaan
	Route::post('showUpdatePertanyaan','API\PertanyaanController@showUpdatePertanyaan'); //Route menampilkan data sebelum update
	Route::post('storeUpdatePertanyaan','API\PertanyaanController@storeUpdatePertanyaan'); //Route post pertanyaan

	/**
	 * Route Jawaban
	 */
	


});
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
	
	
	Route::post('profile','API\EditProfileController@editProfile');
	Route::get('kategori', 'API\KategoriController@getKategori');
	Route::post('details', 'API\UserController@details');

	Route::get('fcm/add/{token}','API\FCMController@insertToken');
	Route::get('fcm/deleting/{token}','API\FCMController@deleteToken');
	Route::get('fcm/getAllNotification','API\FCMController@getAllNotification');

	/**
	 * Route Pertanyaan
	 */
	Route::group(['prefix'=>'pertanyaan'], function(){
		Route::post('/showPertanyaan','API\PertanyaanController@showPertanyaan'); //Route menampilkan seluruh pertanyaan pada Home App
	
		Route::post('/postPertanyaan','API\PertanyaanController@postPertanyaan'); //Route post Pertanyaan
		Route::post('/showUpdatePertanyaan','API\PertanyaanController@showUpdatePertanyaan'); //Route menampilkan data sebelum update
		Route::post('/storeUpdatePertanyaan','API\PertanyaanController@storeUpdatePertanyaan'); //Route post pertanyaan

		Route::get('/showDetailPertanyaan/{id}','API\PertanyaanController@showDetailPertanyaan'); 
		Route::get('/showComment/{id}','API\PertanyaanController@showComment');
		Route::post('/showComment/like/{id}','API\PertanyaanController@commentLike');
		Route::post('/postJawaban','API\PertanyaanController@postComment');
		Route::get('/topOfUser','API\TopOfUserController@getTopOfUser');
	});

	Route::group(['prefix'=>'user'], function(){
		Route::post('/historyQuestionActivity/{id}','API\PertanyaanController@getUserQuestionHistory'); //Route menampilkan seluruh history pertanyaan dan jawaban user
		Route::post('/historyAnswerActivity/{id}','API\PertanyaanController@getUserAnswerHistory'); //Route menampilkan seluruh history pertanyaan dan jawaban user
		Route::post('/historyAnswerActivity/delete/{id}','API\PertanyaanController@deleteAnswer');
		Route::post('/historyQuestionActivity/delete/{id}','API\PertanyaanController@deleteQuestion');
		Route::get('/historyQuestionActivity/edit/{id}','API\PertanyaanController@showEditQuestion');
		Route::post('/historyQuestionActivity/update/{id}','API\PertanyaanController@editQuestion');
		Route::get('/getAllUser', 'API\UserController@getAllUser');

	});
	

	
	


});
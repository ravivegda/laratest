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

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post('register', 'Api\UsersController@create');
Route::post('login', 'Api\UsersController@login');

Route::group(['middleware' => 'auth:api'], function(){
    
    // Users
    // Route::post('/user', 'Api\UsersController@getUser');
    Route::match(['post'], '/user/list', ['as' => 'api_user_list', 'uses' => 'Api\UsersController@listUsers']);
    Route::match(['post'], '/user/edit', ['as' => 'api_user_list', 'uses' => 'Api\UsersController@edit']);
    Route::match(['delete'], '/user/delete', ['as' => 'api_user_list', 'uses' => 'Api\UsersController@delete']);

    // Post
    // Route::post('/post/create', 'Api\PostController@create');
    // Route::match(['post'], '/post/create', ['middleware'=>'CheckApiToken', 'as' => 'api_post_create', 'uses' => 'Api\PostController@create']);
    Route::match(['post'], '/post/create', ['as' => 'api_post_create', 'uses' => 'Api\PostController@create']);
    Route::match(['post'], '/post/edit', ['as' => 'api_post_edit', 'uses' => 'Api\PostController@edit']);
    Route::match(['post'], '/post/view-all-post', ['as' => 'api_post_view_all', 'uses' => 'Api\PostController@viewAllPost']);
    Route::match(['post'], '/post/view-post', ['as' => 'api_post_view', 'uses' => 'Api\PostController@viewPost']);
    Route::match(['delete'], '/post/delete', ['as' => 'api_post_delete', 'uses' => 'Api\PostController@delete']);
});
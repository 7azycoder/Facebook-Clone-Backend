<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $app->get('/', function () use ($app) {
//     echo 'Welcome to Lumen !';
// });

// user routes


$app->group(['middleware' => ['cors']], function () use ($app) {
  $app->post('/signup', 'UsersController@signUp');
  $app->post('/login','UsersController@login');
});

$app->group(['middleware' => ['cors','auth']], function () use ($app) {

  $app->post('/logout', 'UsersController@logout');

	$app->get('/user/', 'UsersController@getCurrentUser');

	$app->get('/user/{user_id}', 'UsersController@getUserById');

	$app->delete('/user/', 'UsersController@deleteCurrentUser');

	$app->get('/search/{name}', 'UsersController@searchUserByName');

	$app->post('/request/{to_user_id}', 'RequestsController@sendRequest');

	$app->delete('/request/{from_user_id}', 'RequestsController@cancelRequest');

	$app->patch('/request/{from_user_id}', 'RequestsController@acceptRequest');

	$app->post('/message', 'MessagesController@sendMessage');

	$app->get('/message', 'MessagesController@getMessages');

	$app->post('/post', 'PostsController@createPost');

	$app->get('/post', 'PostsController@getPosts');

	$app->patch('/post/{id}', 'PostsController@updatePost');

	$app->delete('/post/{id}', 'PostsController@deletePost');

	$app->get('/post/{id}', 'PostsController@getPostById');

	$app->post('/comment', 'CommentsController@createComment');

	$app->get('/comment', 'CommentsController@getComments');

	$app->patch('/comment/{id}', 'CommentsController@updateComment');

	$app->delete('/comment/{id}', 'CommentsController@deleteComment');

	$app->get('/comment/{id}', 'CommentsController@getCommentById');
});

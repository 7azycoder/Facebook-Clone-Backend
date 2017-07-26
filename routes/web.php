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
	$app->get('/user/{user_id}', 'UsersController@getOtherUserById');
	$app->delete('/user/', 'UsersController@deleteCurrentUser');
	$app->get('/search/{name}', 'UsersController@searchUserByName');

	$app->get('/request', 'RequestsController@getRequests');
	$app->post('/request/{to_user_id}', 'RequestsController@sendRequest');
	$app->post('/request/cancel/{to_user_id}', 'RequestsController@cancelSentRequest');
	$app->post('/request/reject/{from_user_id}', 'RequestsController@rejectReceivedRequest');
	$app->post('/request/confirm/{from_user_id}', 'RequestsController@confirmReceivedRequest');
	$app->post('/request/unfriend/{user_id}', 'RequestsController@unFriend');

	$app->post('/message/{to_user_id}', 'MessagesController@sendMessage');
	$app->get('/message/{to_user_id}', 'MessagesController@getMessages');

	$app->post('/post', 'PostsController@createPost');
	$app->get('/post', 'PostsController@getPosts');
	$app->post('/post/update/{id}', 'PostsController@updatePost');
	$app->post('/post/delete/{id}', 'PostsController@deletePost');

	$app->get('/{post_id}/comment', 'CommentsController@getComments');
	$app->post('/{post_id}/comment', 'CommentsController@createComment');
	$app->post('/comment/update/{id}', 'CommentsController@updateComment');
	$app->post('/comment/delete/{id}', 'CommentsController@deleteComment');

	// for testing
	$app->get('/{user_id}/getPosts', 'PostsController@getPostsByUserId');

});

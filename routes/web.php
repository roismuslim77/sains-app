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
use Illuminate\Support\Facades\DB;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1'], function ($api) {

        $api->get('version', function () {
            return response()->json(['status' => 'success', 'message' => env('APP_VERSION')], 200);
        });

        $api->get('dbTime', function () {
            $results = DB::select( "select date_format(now(),'%Y-%m-%d %H:%i:%s') db_time" );
            return response()->json(['status' => 'success', 'message' => $results[0]->db_time], 200);
        });

        $api->group(['middleware' => 'auth'], function() use($api) {
            $api->get('version/auth', function () {
                return response()->json(['status' => 'success', 'message' => env('APP_VERSION')], 200);
            });  

            //classes
            $api->get('classes', 'ClassController@listclass');

            //subjects
            $api->get('subjects', 'SubjectController@listsubject');

            //subject matter
            $api->get('subject/matter', 'SubjectMatterController@listmatter');
            $api->get('subject/matter/{id}', 'SubjectMatterController@showmatter');
            $api->post('subject/matter', 'SubjectMatterController@addmatter');
            $api->post('subject/matter/{id}', 'SubjectMatterController@editmatter');
        });

        //auth
        $api->group(['namespace' => 'Auth'], function () use($api){
            $api->post('/login', 'LoginController@login');
        });
    });
});

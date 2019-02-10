<?php
namespace App\Http\Controllers;

use Dingo\Api\Routing\Router;

/** @var Router $api */

// Router spa #startRegion

//Route::get('/home', 'HomeController@index')->name('home');


// router spa #endregion


$api = app(Router::class);

    $api->version('v1', function (Router $api) {
        // Server Ping
        $api->get('/ping','App\Http\Controllers\DeviceController@ping');
        // Auth Routes
        $api->group(['prefix' => 'auth'], function(Router $api) {
            $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
            $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');
    
            $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
            $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');
    
            $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
            $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
            $api->post('addDevice', 'App\Http\Controllers\DeviceController@add_device');
            $api->post('removeDevice', 'App\Http\Controllers\DeviceController@remove_device');
            $api->get('getDevices', 'App\Http\Controllers\DeviceController@get_devices');
            $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');
        });
        $api->group(['prefix' => 'device'], function(Router $api) {
                // devices Logger 
                $api->post('/deviceLog','App\Http\Controllers\DeviceController@create');
                $api->get('/checkUpdate','App\Http\Controllers\DeviceController@checkUpdate');
                // User / Device
                $api->post('/single','App\Http\Controllers\DeviceController@getDevice');
                $api->post('/mqtt','App\Http\Controllers\DeviceController@mqtt')->middleware(['jwt.auth','App\Http\Middleware\admin']);
                // ->middleware(['jwt.auth','App\Http\Middleware\deviceUser'])
                //admin panel
                $api->post('/changeState','App\Http\Controllers\DeviceController@changeState')->middleware(['jwt.auth','App\Http\Middleware\admin']);
                $api->post('/deleteDev','App\Http\Controllers\DeviceController@deleteDev')->middleware(['jwt.auth','App\Http\Middleware\admin']);
                $api->get('/devices','App\Http\Controllers\DeviceController@list')->middleware(['jwt.auth','App\Http\Middleware\admin']);
                $api->post('/setPassword','App\Http\Controllers\DeviceController@set_passsword');
        });

    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
    //    $api->get('protected', function() {
    //        return response()->json([
    //            'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
    //        ]);
    //    });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
        
        $api->get('checkLogged', function () {
            return response()->json(['message' => 'success'],200); 
        });
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it 2211.'
        ]);
    });
});

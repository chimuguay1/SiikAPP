<?php
include("staff.php");
include("admin.php");
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

Route::get('resize/{type}/{name}', function ($type, $name){

    $file = env('APP_URL').("upload/{$type}/{$name}");

    $local = "upload/{$type}/{$name}";

    if (!File::exists($local)) {
        abort(404);
    }

    $width = 300;

    $file_info = getimagesize($local);

    $ratio = $file_info[0] / $file_info[1];

    $newwidth = $width;
    $newheight = round($newwidth / $ratio);


    $ext = explode(".", $file);
    $ext = strtolower($ext[count($ext) - 1]);
    if ($ext == "jpeg") $ext = "jpg";


    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $white = imagecolorallocate($thumb, 255, 255, 255);
    
    switch ($ext) {
        case "jpg":
            $img = imagecreatefromjpeg($local);
            break;
        case "png":
            $img = imagecreatefrompng($local);
            imagefill($thumb, 0, 0, $white);
            break;
        case "gif":
            $img = imagecreatefromgif($local);
            break;
    }

    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $file_info[0], $file_info[1]);

    if ($ext == "jpeg"){
        header("Content-type: image/jpeg");
        imagejpeg($thumb, null, 8);
    }
    else{
        header("Content-type: image/png");
        imagepng($thumb, null, 8);
    }
    imagedestroy($thumb);
});


Route::group(['namespace' => 'User','prefix' => env('user')], function() {

    Route::get('/','AdminController@index');
    Route::get('login','AdminController@index');
    Route::post('login','AdminController@login');

    Route::group(['middleware' => 'auth'], function() {

        /*
        |-----------------------------------------
        |Dashboard and Account Setting & Logout
        |-----------------------------------------
        */
        Route::get('home','AdminController@home');
        Route::get('setting','AdminController@setting');
        Route::post('setting','AdminController@update');
        Route::get('logout','AdminController@logout');
        Route::get('close','AdminController@close');

        /*
        |--------------------------------------
        |Menu Category
        |--------------------------------------
        */
        Route::resource('category','CategoryController');
        Route::get('category/delete/{id}','CategoryController@delete');
        Route::get('category/status/{id}','CategoryController@status');

        /*
        |--------------------------------------
        |Item Type
        |--------------------------------------
        */
        Route::resource('type','TypeController');
        Route::get('type/delete/{id}','TypeController@delete');

        /*
        |--------------------------------------
        |Manage Addon
        |--------------------------------------
        */
        Route::resource('addon','AddonController');
        Route::get('addon/delete/{id}','AddonController@delete');

        /*
        |--------------------------------------
        |Menu Items
        |--------------------------------------
        */
        Route::resource('item','ItemController');
        Route::get('item/delete/{id}','ItemController@delete');
        Route::get('item/status/{id}','ItemController@status');
        Route::post('itemAddon','ItemController@addon');
        Route::get('export','ItemController@export');
        Route::get('import','ItemController@import');
        Route::post('import','ItemController@_import');

        /*
        |------------------------------
        |Manage Offer
        |------------------------------
        */
        Route::resource('offer','OfferController');
        Route::get('offer/delete/{id}','OfferController@delete');
        Route::get('offer/status/{id}','OfferController@status');

        /*
        |------------------------------
        |Delivery Staff
        |------------------------------
        */
        Route::resource('delivery','DeliveryController');
        Route::get('delivery/delete/{id}','DeliveryController@delete');
        Route::get('delivery/status/{id}','DeliveryController@status');

        /*
        |-------------------------------
        |Manage Orders
        |-------------------------------
        */
        Route::get('order','OrderController@index');
        Route::get('orderStatus','OrderController@orderStatus');
        Route::get('order/print/{id}','OrderController@printBill');
        Route::post('order/dispatched','OrderController@dispatched');
        Route::get('order/edit/{id}','OrderController@edit');
        Route::post('order/edit/{id}','OrderController@_edit');
        Route::get('orderItem','OrderController@orderItem');
        Route::get('getUnit/{id}','OrderController@getUnit');
        Route::get('order/add','OrderController@add');
        Route::post('order/add','OrderController@_add');
        Route::get('getUser/{id}','OrderController@getUser');

    });
});

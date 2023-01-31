<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
/*
Route::middleware('auth:api')->get('/version', function (Request $request) {
    return array("version" => "0.0.1");
});
*/

Route::get('/version', function (Request $request) {
    return array("name"=>"iCandid API", "version" => "2.0");
});

Route::get('/profile/info', "UserController@info");

Route::post('/query','QueryController@query');
Route::post('/query/nocache','QueryController@cachelessquery');
Route::get('/flush','QueryController@flush');
Route::get('/json/{id}','SearchController@getjson');
Route::post('/search','SearchController@search');
Route::post('/search/query','SearchController@query');

Route::get('/profile/user', "UserController@profile");
Route::get('/profile/shelves', "UserController@getshelves");
Route::get('/profile/shelf/{id}/{start}', "UserController@getshelf");


Route::post('/profile/item', "UserController@storeItem");
Route::post('/profile/query', "UserController@storeQuery");
Route::post('/profile/queue', "UserController@queue");

Route::delete('/profile/item/{id}', "UserController@deleteItem");
Route::delete('/profile/shelf/{id}', "UserController@deleteShelf");
Route::delete('/profile/query/{id}', "UserController@deleteQuery");

Route::get('/profile/shelf/{id}/{format?}',"SearchController@exportset");

Route::get('/export/{id}', "ExportController@download");

Route::get('/ddlists',"SearchController@ddlists");

Route::get('/status',"QueryController@status");

Route::post('/admin/status',"ContentController@statussave");
Route::delete('/admin/status',"ContentController@statusdelete");

Route::post('/stats/counter','StatisticsController@counter');
Route::post('/stats/newspaperpie','StatisticsController@newspaperpie');
Route::post('/stats/recordsdaybar','StatisticsController@recordsdaybar');
Route::post('/stats/typepie','StatisticsController@typepie');
Route::post('/stats/articleline','StatisticsController@articleline');
Route::post('/stats/ner','StatisticsController@ner');

Route::post('/form', 'FormController@form');
Route::get('/form/jwt','JWTController@decode');

Route::post('/admin/users/{type}', 'AdminController@usersearch');
Route::post('/admin/user', 'AdminController@usersave');
Route::delete('/admin/user/{id}', 'AdminController@userdelete');
Route::get('/admin/user/export', 'AdminController@usersexport');

Route::post('/admin/datasets', 'AdminController@datasetsearch');
Route::post('/admin/dataset', 'AdminController@datasetsave');

Route::get('/admin/datasetrefresh', 'AdminController@datasetrefresh');

Route::get('/admin/options', 'AdminController@options');

Route::get('/admin/groups', 'AdminController@groups');
Route::post('/admin/group', 'AdminController@groupsave');

Route::get('/admin/labels', 'AdminController@labels');
Route::post('/admin/label', 'AdminController@labelsave');

Route::get('/admin/content', 'ContentController@content');
Route::post('/admin/content', 'ContentController@contentsave');

Route::get('/collection/options', 'CollectionController@options');
Route::post('/collection/search', 'CollectionController@search');

Route::get('/access/{apikey}', 'UserController@access');

Route::get('/content/{contentcode}', 'ContentController@get');

Route::get('/jwt/validate','JWTController@validation');
Route::get('/jwt/decode','JWTController@decode');

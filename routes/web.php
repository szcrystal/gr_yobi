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

Route::get('/', 'Main\HomeController@index');



//DashBoard ===================================================
Route::get('dashboard', 'DashBoard\MainController@index');

Route::get('dashboard/login', 'DashBoard\LoginController@index');
Route::post('dashboard/login', 'DashBoard\LoginController@postLogin');

Route::get('dashboard/register', 'DashBoard\MainController@getRegister');
Route::get('dashboard/register/{id}', 'DashBoard\MainController@getRegister');
Route::post('dashboard/register', 'DashBoard\MainController@postRegister');
Route::delete('dashboard/register/{id}', 'DashBoard\MainController@destroy');
Route::get('dashboard/logout', 'DashBoard\MainController@getLogout');

//Setting
Route::resource('dashboard/settings/index', 'DashBoard\SettingController');

//Top Setting
Route::resource('dashboard/settings/top-settings', 'DashBoard\TopSettingController');

//MailTemplate
Route::resource('dashboard/settings/mails', 'DashBoard\MailTemplateController');
    
//Consignor
Route::resource('dashboard/consignors', 'DashBoard\ConsignorController');

//DeliveryGroup
Route::resource('dashboard/dgs', 'DashBoard\DeliveryGroupController');
Route::get('dashboard/dgs/fee/{dgId}', 'DashBoard\DeliveryGroupController@getFee');
Route::post('dashboard/dgs/fee/{dgId}', 'DashBoard\DeliveryGroupController@postFee');

//DeliveryCompany
Route::resource('dashboard/dcs', 'DashBoard\DeliveryCompanyController');

//Prefecture
//Route::resource('dashboard/prefectures', 'DashBoard\PrefectureController');

//Item
Route::get('dashboard/items/csv', 'DashBoard\ItemController@getCsv');
Route::post('dashboard/items/script', 'DashBoard\ItemController@postScript');
Route::get('dashboard/items/pot-set', 'DashBoard\ItemController@potSetIndex');
Route::resource('dashboard/items', 'DashBoard\ItemController');

//ItemUpper
Route::resource('dashboard/upper', 'DashBoard\ItemUpperController');


//Category
Route::resource('dashboard/post-categories/sec', 'DashBoard\PostCategorySecController');
Route::resource('dashboard/post-categories', 'DashBoard\PostCategoryController');
Route::resource('dashboard/categories/sub', 'DashBoard\CategorySecondController');
Route::resource('dashboard/categories', 'DashBoard\CategoryController');


//Tag
Route::resource('dashboard/tags', 'DashBoard\TagController');

//Contact
Route::resource('dashboard/contacts', 'DashBoard\ContactController');

//Sale
Route::get('dashboard/sales/csv', 'DashBoard\SaleController@getCsv');
Route::get('dashboard/sales/compare', 'DashBoard\SaleController@saleCompare');
Route::get('dashboard/sales/order/{orderNum}', 'DashBoard\SaleController@saleOrder');
Route::post('dashboard/sales/order', 'DashBoard\SaleController@postSaleOrder');
Route::resource('dashboard/sales', 'DashBoard\SaleController');

//User
Route::get('dashboard/users/csv', 'DashBoard\UserController@getCsv');
Route::resource('dashboard/users', 'DashBoard\UserController');

//Fix
Route::resource('dashboard/fixes', 'DashBoard\FixController');

//MailMagazine
Route::resource('dashboard/magazines', 'DashBoard\MailMagazineController');

//Post
Route::resource('dashboard/posts', 'DashBoard\PostController');


//Main =========================================================
//Fix Page
if(Schema::hasTable('fixes')) {
    //use App\Fix;
    $fixes = DB::table('fixes')->where('open_status', 1)->get();
    foreach($fixes as $fix) {
        Route::get($fix->slug, 'Main\HomeController@getFix');
    }
}

//LookFor Search Page (Only SP)
Route::get('lookfor', 'Main\HomeController@index');

//DeliFeeTable
Route::get('deli-fee/{dgId}', 'Main\HomeController@showDeliFeeTable');


//Sale Item
Route::get('sale-items', 'Main\HomeController@uniqueArchive');

//New Item
Route::get('new-items', 'Main\HomeController@uniqueArchive');

//Ranking
Route::get('ranking', 'Main\HomeController@uniqueArchive');
Route::get('ranking-ueki', 'Main\HomeController@uniqueArchive');

//Recent Check
Route::get('recent-items', 'Main\HomeController@uniqueArchive');

//Recent Check
Route::get('item/packing', 'Main\HomeController@uniqueArchive');

//Recommend Info
Route::get('recommend-info', 'Main\HomeController@recomInfo');

//Category
Route::get('category/{slug}', 'Main\HomeController@category');
Route::get('category/{slug}/{subSlug}', 'Main\HomeController@subCategory');

//Tag
Route::get('tag/{slug}', 'Main\HomeController@tag');

//Search
Route::get('search', 'Main\SearchController@index');

//Contact
Route::post('contact/end', 'Main\ContactController@postEnd');
Route::resource('contact', 'Main\ContactController');

//Single
Route::get('/item/{id}', 'Main\SingleController@index');
Route::post('/item/script', 'Main\SingleController@postScript');

//CacheFavorite
Route::get('favorite', 'Main\SingleController@favIndex');
Route::post('favorite-del', 'Main\SingleController@postFavDel');

//Post
Route::get('post/category/{slug}/{slugSec?}', 'Main\PostController@category');
//Route::get('post/view-rank', 'Main\PostController@viewRank');
Route::resource('post', 'Main\PostController');

//Shop Cart
Route::post('/cart/form', 'Main\SingleController@postForm');
Route::post('/cart/payment', 'Main\SingleController@postCart');
Route::get('/cart/thankyou', 'Main\SingleController@endCart');


Route::post('/shop/cart', 'Cart\CartController@postCart');
Route::get('/shop/cart', 'Cart\CartController@postCart');

Route::post('/shop/form', 'Cart\CartController@postForm');
Route::get('/shop/form', 'Cart\CartController@postForm');

Route::post('/shop/confirm', 'Cart\CartController@postConfirm');
Route::get('/shop/confirm', 'Cart\CartController@postConfirm');

//クレカ
Route::post('/shop/paydo', 'Cart\PaymentController@postCardPay');
Route::get('/shop/error', 'Cart\CartController@getShopError');

//Amzn
Route::post('/shop/amznpay', 'Cart\PaymentController@setAmznPay');
Route::post('/shop/amznpay-retry', 'Cart\PaymentController@retrySetAmznPay');

//後払い
Route::post('/shop/afterdo', 'Cart\CartController@postAfterPay');

Route::post('/shop/thankyou', 'Cart\CartController@getThankyou');
Route::get('/shop/thankyou', 'Cart\CartController@getThankyou');

Route::get('/shop/clear', 'Cart\CartController@getClear');

//Route::resource('/shop/cart', 'Cart\CartController');

//MyPage
Route::get('/mypage', 'MyPage\MyPageController@index');

Route::get('/mypage/history', 'MyPage\MyPageController@history');
Route::get('/mypage/history/{id}', 'MyPage\MyPageController@showHistory');

Route::get('/mypage/register', 'MyPage\MyPageController@getRegister');
Route::post('/mypage/register', 'MyPage\MyPageController@postRegister');
Route::post('/mypage/register/end', 'MyPage\MyPageController@registerEnd');

Route::get('/mypage/favorite', 'MyPage\MyPageController@favorite');

Route::get('/mypage/optout', 'MyPage\MyPageController@getOptout');
Route::post('/mypage/optout', 'MyPage\MyPageController@postOptout');

//Route::get('logout', 'Auth\LoginController@getLogout');

Auth::routes();
Route::get('/register', 'MyPage\MyPageController@getRegister');
Route::post('/register', 'MyPage\MyPageController@postRegister');
Route::post('/register/end', 'MyPage\MyPageController@registerEnd');


Route::get('/home', 'HomeController@index')->name('home');

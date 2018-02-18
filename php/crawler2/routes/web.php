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

#Route::get('/', 'CrawlerController2@index')->name('crawler2');
#Route::get('/index', 'CrawlerController2@index')->name('crawler2');
Route::get('/', 'CrawlerController2@getLinks')->name('crawler2');
Route::get('/crawler/getLinks', 'CrawlerController2@getLinks')->name('crawler2');
Route::get('/crawler/crawlALink', 'CrawlerController2@crawlALink')->name('crawler2');
Route::get('/crawler/count', 'CrawlerController2@countInserts')->name('crawler2');
Route::get('/pci', 'CrawlerPCIController@getLinks')->name('crawlerPCI');
Route::get('/crawlit', 'CrawlerPCIController@crawlIt')->name('crawlerPCI');
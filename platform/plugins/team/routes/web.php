<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Team\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'teams', 'as' => 'team.'], function () {
            Route::resource('', 'TeamController')->parameters(['' => 'team']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'TeamController@deletes',
                'permission' => 'team.destroy',
            ]);
        });
    });
});

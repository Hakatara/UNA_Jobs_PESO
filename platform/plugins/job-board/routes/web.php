<?php

use Botble\Base\Facades\BaseHelper;
use Botble\JobBoard\Http\Controllers\AccountEducationController;
use Botble\JobBoard\Http\Controllers\AccountExperienceController;
use Botble\JobBoard\Http\Controllers\CouponController;
use Botble\JobBoard\Http\Controllers\CustomFieldController;
use Botble\JobBoard\Http\Controllers\ExportAccountController;
use Botble\JobBoard\Http\Controllers\ImportAccountController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\JobBoard\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix() . '/job-board', 'middleware' => 'auth'], function () {
        Route::get('settings', [
            'as' => 'job-board.settings',
            'uses' => 'JobBoardController@getSettings',
        ]);

        Route::post('settings', [
            'as' => 'job-board.settings.post',
            'uses' => 'JobBoardController@postSettings',
            'permission' => 'job-board.settings',
        ]);

        Route::group(['prefix' => 'jobs', 'as' => 'jobs.'], function () {
            Route::resource('', 'JobController')->parameters(['' => 'job']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'JobController@deletes',
                'permission' => 'jobs.destroy',
            ]);

            Route::get('{id}/analytics', [
                'as' => 'analytics',
                'uses' => 'JobController@analytics',
                'permission' => 'jobs.index',
            ])->wherePrimaryKey();
        });

        Route::group(['prefix' => 'job-types', 'as' => 'job-types.'], function () {
            Route::resource('', 'JobTypeController')
                ->parameters(['' => 'job-type']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'JobTypeController@deletes',
                'permission' => 'job-types.destroy',
            ]);
        });

        Route::group(['prefix' => 'job-skills', 'as' => 'job-skills.'], function () {
            Route::resource('', 'JobSkillController')->parameters(['' => 'job-skill']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'JobSkillController@deletes',
                'permission' => 'job-skills.destroy',
            ]);
        });

        Route::group(['prefix' => 'job-shifts', 'as' => 'job-shifts.'], function () {
            Route::resource('', 'JobShiftController')->parameters(['' => 'job-shift']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'JobShiftController@deletes',
                'permission' => 'job-shifts.destroy',
            ]);
        });

        Route::group(['prefix' => 'job-experiences', 'as' => 'job-experiences.'], function () {
            Route::resource('', 'JobExperienceController')->parameters(['' => 'job-experience']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'JobExperienceController@deletes',
                'permission' => 'job-experiences.destroy',
            ]);
        });

        Route::group(['prefix' => 'language-levels', 'as' => 'language-levels.'], function () {
            Route::resource('', 'LanguageLevelController')->parameters(['' => 'language-level']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'LanguageLevelController@deletes',
                'permission' => 'language-levels.destroy',
            ]);
        });

        Route::group(['prefix' => 'career-levels', 'as' => 'career-levels.'], function () {
            Route::resource('', 'CareerLevelController')
                ->parameters(['' => 'career-level']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'CareerLevelController@deletes',
                'permission' => 'career-levels.destroy',
            ]);
        });

        Route::group(['prefix' => 'functional-areas', 'as' => 'functional-areas.'], function () {
            Route::resource('', 'FunctionalAreaController')
                ->parameters(['' => 'functional-area']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'FunctionalAreaController@deletes',
                'permission' => 'functional-areas.destroy',
            ]);
        });

        Route::group(['prefix' => 'job-categories', 'as' => 'job-categories.'], function () {
            Route::resource('', 'CategoryController')
                ->parameters(['' => 'job-category']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'CategoryController@deletes',
                'permission' => 'job-categories.destroy',
            ]);
        });

        Route::group(['prefix' => 'degree-types', 'as' => 'degree-types.'], function () {
            Route::resource('', 'DegreeTypeController')
                ->parameters(['' => 'degree-type']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'DegreeTypeController@deletes',
                'permission' => 'degree-types.destroy',
            ]);
        });

        Route::group(['prefix' => 'degree-levels', 'as' => 'degree-levels.'], function () {
            Route::resource('', 'DegreeLevelController')
                ->parameters(['' => 'degree-levels']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'DegreeLevelController@deletes',
                'permission' => 'degree-levels.destroy',
            ]);
        });

        Route::group(['prefix' => 'tags', 'as' => 'job-board.tag.'], function () {
            Route::resource('', 'TagController')
                ->parameters(['' => 'tag']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'TagController@deletes',
                'permission' => 'job-board.tag.destroy',
            ]);

            Route::get('all', [
                'as' => 'all',
                'uses' => 'TagController@getAllTags',
                'permission' => 'job-board.tag.index',
            ]);
        });

        Route::group(['prefix' => 'accounts', 'as' => 'accounts.'], function () {
            Route::resource('', 'AccountController')->parameters(['' => 'account']);

            Route::group(['prefix' => 'educations', 'as' => 'educations.'], function () {
                Route::resource('', AccountEducationController::class)->parameters(['' => 'education']);
                Route::get('{id}/{accountId}/edit-modal', [AccountEducationController::class, 'editModal'])->name(
                    'edit-modal'
                )->wherePrimaryKey(['id', 'accountId']);
            });

            Route::group(['prefix' => 'experiences', 'as' => 'experiences.'], function () {
                Route::resource('', AccountExperienceController::class)->parameters(['' => 'experience']);
                Route::get('{id}/{accountId}/edit-modal', [AccountExperienceController::class, 'editModal'])->name(
                    'edit-modal'
                )->wherePrimaryKey(['id', 'accountId']);
            });

            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'AccountController@deletes',
                'permission' => 'accounts.destroy',
            ]);

            Route::get('list', [
                'as' => 'list',
                'uses' => 'AccountController@getList',
                'permission' => 'accounts.index',
            ]);

            Route::post('credits/{id}', [
                'as' => 'credits.add',
                'uses' => 'TransactionController@postCreate',
                'permission' => 'accounts.edit',
            ])->wherePrimaryKey();

            Route::get('all-employers', [
                'as' => 'all-employers',
                'uses' => 'AccountController@getAllEmployers',
                'permission' => 'accounts.index',
            ]);
        });

        Route::group(['prefix' => 'packages', 'as' => 'packages.'], function () {
            Route::resource('', 'PackageController')->parameters(['' => 'package']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'PackageController@deletes',
                'permission' => 'packages.destroy',
            ]);
        });

        Route::group(['prefix' => 'companies', 'as' => 'companies.'], function () {
            Route::resource('', 'CompanyController')->parameters(['' => 'company']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'CompanyController@deletes',
                'permission' => 'companies.destroy',
            ]);

            Route::get('list', [
                'as' => 'list',
                'uses' => 'CompanyController@getList',
                'permission' => 'companies.index',
            ]);

            Route::get('all', [
                'as' => 'all',
                'uses' => 'CompanyController@getAllCompanies',
                'permission' => 'companies.index',
            ]);

            Route::get('{id}/analytics', [
                'as' => 'analytics',
                'uses' => 'CompanyController@analytics',
                'permission' => 'companies.index',
            ])->wherePrimaryKey();
        });

        Route::get('ajax/companies/{id}', 'CompanyController@ajaxGetCompany')
            ->name('ajax.company.show')
            ->wherePrimaryKey();

        Route::post('ajax/companies', 'CompanyController@ajaxCreateCompany')
            ->name('ajax.company.create');

        Route::group(['prefix' => 'job-applications', 'as' => 'job-applications.'], function () {
            Route::resource('', 'JobApplicationController')
                ->except(['create', 'store'])
                ->parameters(['' => 'job-application']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'JobApplicationController@deletes',
                'permission' => 'job-applications.destroy',
            ]);

            Route::get('download-cv/{id}', [
                'as' => 'download-cv',
                'uses' => 'JobApplicationController@downloadCv',
                'permission' => false,
            ])->wherePrimaryKey();
        });

        Route::group(['prefix' => 'invoices', 'as' => 'invoice.'], function () {
            Route::resource('', 'InvoiceController')
                ->parameters(['' => 'invoice'])
                ->except(['create', 'store', 'update']);

            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'InvoiceController@deletes',
                'permission' => 'invoice.destroy',
            ]);

            Route::get('generate-invoice/{id}', [
                'as' => 'generate-invoice',
                'uses' => 'InvoiceController@getGenerateInvoice',
                'permission' => 'invoice.edit',
            ])->wherePrimaryKey();
        });

        Route::prefix('invoice-template')->name('invoice-template.')->group(function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'InvoiceTemplateController@index',
                'permission' => 'invoice-template.index',
            ]);

            Route::put('/', [
                'as' => 'update',
                'uses' => 'InvoiceTemplateController@update',
                'permission' => 'invoice-template.index',
            ]);

            Route::post('reset', [
                'as' => 'reset',
                'uses' => 'InvoiceTemplateController@reset',
                'permission' => 'invoice-template.index',
            ]);

            Route::get('preview', [
                'as' => 'preview',
                'uses' => 'InvoiceTemplateController@preview',
                'permission' => 'invoice-template.index',
            ]);
        });

        Route::prefix('custom-fields')->name('job-board.custom-fields.')->group(function () {
            Route::resource('', CustomFieldController::class)->parameters(['' => 'custom-field']);

            Route::get('info', [
                'as' => 'get-info',
                'uses' => 'CustomFieldController@getInfo',
                'permission' => false,
            ]);

            Route::delete('deletes', [
                'as' => 'deletes',
                'uses' => 'CustomFieldController@deletes',
                'permission' => 'job-board.custom-fields.destroy',
            ]);
        });

        Route::group(['prefix' => 'import/jobs', 'as' => 'import-jobs.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'ImportJobController@index',
            ]);

            Route::post('/', [
                'as' => 'store',
                'uses' => 'ImportJobController@store',
                'permission' => 'import-jobs.index',
            ]);

            Route::post('download-template', [
                'as' => 'download-template',
                'uses' => 'ImportJobController@downloadTemplate',
                'permission' => 'import-jobs.index',
            ]);
        });

        Route::group(['prefix' => 'export-jobs', 'as' => 'export-jobs.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'ExportJobController@index',
                'permission' => 'export-jobs.index',
            ]);

            Route::post('/', [
                'as' => 'index.post',
                'uses' => 'ExportJobController@export',
                'permission' => 'export-jobs.index',
            ]);
        });

        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('import', [ImportAccountController::class, 'index'])->name('import');
            Route::post('import', [ImportAccountController::class, 'import']);
            Route::get('export', [ExportAccountController::class, 'index'])->name('export');
            Route::post('export', [ExportAccountController::class, 'export']);
            Route::post('download-template', [ImportAccountController::class, 'downloadTemplate'])->name('download-template');
        });

        Route::group(['prefix' => 'import/companies', 'as' => 'import-companies.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'ImportCompanyController@index',
            ]);

            Route::post('/', [
                'as' => 'store',
                'uses' => 'ImportCompanyController@store',
                'permission' => 'import-companies.index',
            ]);

            Route::post('download-template', [
                'as' => 'download-template',
                'uses' => 'ImportCompanyController@downloadTemplate',
                'permission' => 'import-companies.index',
            ]);
        });

        Route::group(['prefix' => 'export-companies', 'as' => 'export-companies.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'ExportCompanyController@index',
                'permission' => 'export-companies.index',
            ]);

            Route::post('/', [
                'as' => 'index.post',
                'uses' => 'ExportCompanyController@export',
                'permission' => 'export-companies.index',
            ]);
        });

        Route::group(['prefix' => 'coupons', 'as' => 'coupons.'], function () {
            Route::resource('', CouponController::class)
                ->parameters(['' => 'coupon']);

            Route::post('generate-coupon', [
                'as' => 'generate-coupon',
                'uses' => 'CouponController@generateCouponCode',
                'permission' => 'coupons.index',
            ]);

            Route::delete('deletes', [
                'as' => 'deletes',
                'uses' => 'CouponController@deletes',
                'permission' => 'coupons.destroy',
            ]);
        });
    });
});

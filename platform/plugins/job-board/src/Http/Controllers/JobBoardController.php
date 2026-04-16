<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Http\Requests\UpdateSettingsRequest;
use Botble\JobBoard\Repositories\Interfaces\CurrencyInterface;
use Botble\JobBoard\Services\StoreCurrenciesService;
use Botble\Setting\Supports\SettingStore;

class JobBoardController extends BaseController
{
    public function __construct(protected CurrencyInterface $currencyRepository)
    {
    }

    public function getSettings()
    {
        PageTitle::setTitle(trans('plugins/job-board::job-board.settings'));

        Assets::addScripts(['jquery-ui'])
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/currencies.js')
            ->addStylesDirectly('vendor/core/plugins/job-board/css/currencies.css');

        $currencies = $this->currencyRepository
            ->getAllCurrencies()
            ->toArray();

        return view('plugins/job-board::settings.index', compact('currencies'));
    }

    public function postSettings(
        UpdateSettingsRequest $request,
        BaseHttpResponse $response,
        StoreCurrenciesService $service,
        SettingStore $settingStore
    ) {
        foreach ($request->except(['_token', 'currencies', 'deleted_currencies']) as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();
        $currencies = json_decode($request->input('currencies'), true) ?: [];
        $deletedCurrencies = json_decode($request->input('deleted_currencies', []), true) ?: [];

        $service->execute($currencies, $deletedCurrencies);

        return $response
            ->setNextUrl(route('job-board.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}

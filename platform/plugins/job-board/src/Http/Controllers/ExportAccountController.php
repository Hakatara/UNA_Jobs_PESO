<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\JobBoard\Exports\AccountsExport;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAccountController extends BaseController
{
    public function index(AccountInterface $accountRepository): View
    {
        PageTitle::setTitle(trans('plugins/job-board::candidate.export.name'));

        Assets::addScriptsDirectly(['vendor/core/plugins/job-board/js/export.js']);

        $total = $accountRepository->count();

        return view('plugins/job-board::accounts.export', compact('total'));
    }

    public function export(AccountsExport $candidatesExport): BinaryFileResponse
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        return $candidatesExport->download('export_accounts.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
    }
}

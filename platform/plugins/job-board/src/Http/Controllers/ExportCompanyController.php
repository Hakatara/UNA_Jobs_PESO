<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\JobBoard\Exports\CompaniesExport;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Maatwebsite\Excel\Excel;

class ExportCompanyController extends BaseController
{
    public function index(CompanyInterface $companyRepository)
    {
        PageTitle::setTitle(trans('plugins/job-board::export.companies.name'));

        Assets::addScriptsDirectly(['vendor/core/plugins/job-board/js/export.js']);

        $totalCompanies = $companyRepository->count();

        return view('plugins/job-board::export.companies', compact('totalCompanies'));
    }

    public function export(CompaniesExport $companiesExport)
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        return $companiesExport->download('export_companies.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
    }
}

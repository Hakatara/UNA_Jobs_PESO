<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\JobBoard\Exports\JobsExport;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Maatwebsite\Excel\Excel;

class ExportJobController extends BaseController
{
    public function index(JobInterface $jobRepository)
    {
        PageTitle::setTitle(trans('plugins/job-board::export.jobs.name'));

        Assets::addScriptsDirectly(['vendor/core/plugins/job-board/js/export.js']);

        $totalJobs = $jobRepository->count();

        return view('plugins/job-board::export.jobs', compact('totalJobs'));
    }

    public function export(JobsExport $jobsExport)
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        return $jobsExport->download('export_products.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
    }
}

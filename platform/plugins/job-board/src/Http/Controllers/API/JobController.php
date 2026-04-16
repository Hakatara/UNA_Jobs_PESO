<?php

namespace Botble\JobBoard\Http\Controllers\API;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Http\Resources\JobResource;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JobController extends Controller
{
    public function __construct(protected JobInterface $jobRepository)
    {
    }

    public function index(Request $request, BaseHttpResponse $response)
    {
        $with = [
            'slugable',
            'company',
            'company.slugable',
            'company.accounts',
        ];
        if (is_plugin_active('location')) {
            $with = array_merge($with, [
                'state',
                'city',
                'company.country',
                'company.state',
                'company.city',
            ]);
        }
        $condition = JobBoardHelper::getJobDisplayQueryConditions();
        if ($request->input('company_id')) {
            $condition['jb_jobs.company_id'] = $request->input('company_id');
        }
        $filters = [
            'condition' => $condition,
            'order_by' => [
                'jb_jobs.created_at' => 'desc',
            ],
        ];
        $params = [
            'paginate' => [
                'per_page' => 12,
                'current_paged' => $request->integer('page'),
            ],
            'with' => $with,
        ];

        $jobs = $this->jobRepository->getJobs($filters, $params);

        return $response
            ->setData(JobResource::collection($jobs))
            ->toApiResponse();
    }
}

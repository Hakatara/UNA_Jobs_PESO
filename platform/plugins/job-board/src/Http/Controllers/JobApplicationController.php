<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Forms\JobApplicationForm;
use Botble\JobBoard\Http\Requests\EditJobApplicationRequest;
use Botble\JobBoard\Repositories\Interfaces\JobApplicationInterface;
use Botble\JobBoard\Tables\JobApplicationTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends BaseController
{
    public function __construct(protected JobApplicationInterface $jobApplicationRepository)
    {
    }

    public function index(JobApplicationTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-application.name'));

        return $table->renderTable();
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-application.edit'));

        $jobApplication = $this->jobApplicationRepository->findOrFail($id);

        return $formBuilder->create(JobApplicationForm::class, ['model' => $jobApplication])->renderForm();
    }

    public function update(int|string $id, EditJobApplicationRequest $request, BaseHttpResponse $response)
    {
        $jobApplication = $this->jobApplicationRepository->findOrFail($id);

        $jobApplication->fill($request->input());

        $this->jobApplicationRepository->createOrUpdate($jobApplication);

        event(new UpdatedContentEvent(JOB_APPLICATION_MODULE_SCREEN_NAME, $request, $jobApplication));

        return $response
            ->setPreviousUrl(route('job-applications.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Request $request, int $id, BaseHttpResponse $response)
    {
        try {
            $jobApplication = $this->jobApplicationRepository->findById($id);
            $this->jobApplicationRepository->delete($jobApplication);
            event(new DeletedContentEvent(JOB_APPLICATION_MODULE_SCREEN_NAME, $request, $jobApplication));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $jobApplication = $this->jobApplicationRepository->findOrFail($id);
            $this->jobApplicationRepository->delete($jobApplication);
            event(new DeletedContentEvent(JOB_APPLICATION_MODULE_SCREEN_NAME, $request, $jobApplication));
        }

        return $response
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function downloadCv(int|string $id)
    {
        $jobApplication = $this->jobApplicationRepository->findOrFail($id);

        if ($jobApplication->resume) {
            return Storage::download($jobApplication->resume);
        }

        $account = $jobApplication->account;

        if ($account->id && $account->resume) {
            return Storage::download($account->resume);
        }

        return Storage::download($jobApplication->resume);
    }
}

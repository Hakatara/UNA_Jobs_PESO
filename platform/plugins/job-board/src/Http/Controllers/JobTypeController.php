<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Forms\JobTypeForm;
use Botble\JobBoard\Http\Requests\JobTypeRequest;
use Botble\JobBoard\Repositories\Interfaces\JobTypeInterface;
use Botble\JobBoard\Tables\JobTypeTable;
use Exception;
use Illuminate\Http\Request;

class JobTypeController extends BaseController
{
    public function __construct(protected JobTypeInterface $jobTypeRepository)
    {
    }

    public function index(JobTypeTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-type.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-type.create'));

        return $formBuilder->create(JobTypeForm::class)->renderForm();
    }

    public function store(JobTypeRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            $this->jobTypeRepository->getModel()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $jobType = $this->jobTypeRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(JOB_TYPE_MODULE_SCREEN_NAME, $request, $jobType));

        return $response
            ->setPreviousUrl(route('job-types.index'))
            ->setNextUrl(route('job-types.edit', $jobType->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $jobType = $this->jobTypeRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $jobType));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $jobType->name]));

        return $formBuilder->create(JobTypeForm::class, ['model' => $jobType])->renderForm();
    }

    public function update(int|string $id, JobTypeRequest $request, BaseHttpResponse $response)
    {
        $jobType = $this->jobTypeRepository->findOrFail($id);

        if ($request->input('is_default')) {
            $this->jobTypeRepository->getModel()->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $jobType->fill($request->input());

        $this->jobTypeRepository->createOrUpdate($jobType);

        event(new UpdatedContentEvent(JOB_TYPE_MODULE_SCREEN_NAME, $request, $jobType));

        return $response
            ->setPreviousUrl(route('job-types.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $jobType = $this->jobTypeRepository->findOrFail($id);

            $this->jobTypeRepository->delete($jobType);

            event(new DeletedContentEvent(JOB_TYPE_MODULE_SCREEN_NAME, $request, $jobType));

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
            $jobType = $this->jobTypeRepository->findOrFail($id);
            $this->jobTypeRepository->delete($jobType);
            event(new DeletedContentEvent(JOB_TYPE_MODULE_SCREEN_NAME, $request, $jobType));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

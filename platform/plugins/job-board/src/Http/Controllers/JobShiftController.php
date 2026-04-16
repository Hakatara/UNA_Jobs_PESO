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
use Botble\JobBoard\Forms\JobShiftForm;
use Botble\JobBoard\Http\Requests\JobShiftRequest;
use Botble\JobBoard\Repositories\Interfaces\JobShiftInterface;
use Botble\JobBoard\Tables\JobShiftTable;
use Exception;
use Illuminate\Http\Request;

class JobShiftController extends BaseController
{
    public function __construct(protected JobShiftInterface $jobShiftRepository)
    {
    }

    public function index(JobShiftTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-shift.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-shift.create'));

        return $formBuilder->create(JobShiftForm::class)->renderForm();
    }

    public function store(JobShiftRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            $this->jobShiftRepository->getModel()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $jobShift = $this->jobShiftRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));

        return $response
            ->setPreviousUrl(route('job-shifts.index'))
            ->setNextUrl(route('job-shifts.edit', $jobShift->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $jobShift = $this->jobShiftRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $jobShift));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $jobShift->name]));

        return $formBuilder->create(JobShiftForm::class, ['model' => $jobShift])->renderForm();
    }

    public function update(int|string $id, JobShiftRequest $request, BaseHttpResponse $response)
    {
        $jobShift = $this->jobShiftRepository->findOrFail($id);

        if ($request->input('is_default')) {
            $this->jobShiftRepository->getModel()->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $jobShift->fill($request->input());

        $this->jobShiftRepository->createOrUpdate($jobShift);

        event(new UpdatedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));

        return $response
            ->setPreviousUrl(route('job-shifts.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $jobShift = $this->jobShiftRepository->findOrFail($id);

            $this->jobShiftRepository->delete($jobShift);

            event(new DeletedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));

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
            $jobShift = $this->jobShiftRepository->findOrFail($id);
            $this->jobShiftRepository->delete($jobShift);
            event(new DeletedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

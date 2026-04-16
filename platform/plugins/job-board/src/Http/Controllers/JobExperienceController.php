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
use Botble\JobBoard\Forms\JobExperienceForm;
use Botble\JobBoard\Http\Requests\JobExperienceRequest;
use Botble\JobBoard\Repositories\Interfaces\JobExperienceInterface;
use Botble\JobBoard\Tables\JobExperienceTable;
use Exception;
use Illuminate\Http\Request;

class JobExperienceController extends BaseController
{
    public function __construct(protected JobExperienceInterface $jobExperienceRepository)
    {
    }

    public function index(JobExperienceTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-experience.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-experience.create'));

        return $formBuilder->create(JobExperienceForm::class)->renderForm();
    }

    public function store(JobExperienceRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            $this->jobExperienceRepository->getModel()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $jobExperience = $this->jobExperienceRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));

        return $response
            ->setPreviousUrl(route('job-experiences.index'))
            ->setNextUrl(route('job-experiences.edit', $jobExperience->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $jobExperience = $this->jobExperienceRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $jobExperience));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $jobExperience->name]));

        return $formBuilder->create(JobExperienceForm::class, ['model' => $jobExperience])->renderForm();
    }

    public function update(int|string $id, JobExperienceRequest $request, BaseHttpResponse $response)
    {
        $jobExperience = $this->jobExperienceRepository->findOrFail($id);

        if ($request->input('is_default')) {
            $this->jobExperienceRepository->getModel()->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $jobExperience->fill($request->input());

        $this->jobExperienceRepository->createOrUpdate($jobExperience);

        event(new UpdatedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));

        return $response
            ->setPreviousUrl(route('job-experiences.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $jobExperience = $this->jobExperienceRepository->findOrFail($id);

            $this->jobExperienceRepository->delete($jobExperience);

            event(new DeletedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));

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
            $jobExperience = $this->jobExperienceRepository->findOrFail($id);
            $this->jobExperienceRepository->delete($jobExperience);
            event(new DeletedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

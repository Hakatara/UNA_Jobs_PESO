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
use Botble\JobBoard\Forms\CareerLevelForm;
use Botble\JobBoard\Http\Requests\CareerLevelRequest;
use Botble\JobBoard\Repositories\Interfaces\CareerLevelInterface;
use Botble\JobBoard\Tables\CareerLevelTable;
use Exception;
use Illuminate\Http\Request;

class CareerLevelController extends BaseController
{
    public function __construct(protected CareerLevelInterface $careerLevelRepository)
    {
    }

    public function index(CareerLevelTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::career-level.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::career-level.create'));

        return $formBuilder->create(CareerLevelForm::class)->renderForm();
    }

    public function store(CareerLevelRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            $this->careerLevelRepository->getModel()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $careerLevel = $this->careerLevelRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));

        return $response
            ->setPreviousUrl(route('career-levels.index'))
            ->setNextUrl(route('career-levels.edit', $careerLevel->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $careerLevel = $this->careerLevelRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $careerLevel));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $careerLevel->name]));

        return $formBuilder->create(CareerLevelForm::class, ['model' => $careerLevel])->renderForm();
    }

    public function update(int|string $id, CareerLevelRequest $request, BaseHttpResponse $response)
    {
        $careerLevel = $this->careerLevelRepository->findOrFail($id);

        if ($request->input('is_default')) {
            $this->careerLevelRepository->getModel()->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $careerLevel->fill($request->input());

        $this->careerLevelRepository->createOrUpdate($careerLevel);

        event(new UpdatedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));

        return $response
            ->setPreviousUrl(route('career-levels.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $careerLevel = $this->careerLevelRepository->findOrFail($id);

            $this->careerLevelRepository->delete($careerLevel);

            event(new DeletedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));

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
            $careerLevel = $this->careerLevelRepository->findOrFail($id);
            $this->careerLevelRepository->delete($careerLevel);
            event(new DeletedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

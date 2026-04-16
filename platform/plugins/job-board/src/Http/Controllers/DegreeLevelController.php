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
use Botble\JobBoard\Forms\DegreeLevelForm;
use Botble\JobBoard\Http\Requests\DegreeLevelRequest;
use Botble\JobBoard\Repositories\Interfaces\DegreeLevelInterface;
use Botble\JobBoard\Tables\DegreeLevelTable;
use Exception;
use Illuminate\Http\Request;

class DegreeLevelController extends BaseController
{
    public function __construct(protected DegreeLevelInterface $degreeLevelRepository)
    {
    }

    public function index(DegreeLevelTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::degree-level.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::degree-level.create'));

        return $formBuilder->create(DegreeLevelForm::class)->renderForm();
    }

    public function store(DegreeLevelRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            $this->degreeLevelRepository->getModel()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $degreeLevel = $this->degreeLevelRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(DEGREE_LEVEL_MODULE_SCREEN_NAME, $request, $degreeLevel));

        return $response
            ->setPreviousUrl(route('degree-levels.index'))
            ->setNextUrl(route('degree-levels.edit', $degreeLevel->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $degreeLevel = $this->degreeLevelRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $degreeLevel));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $degreeLevel->name]));

        return $formBuilder->create(DegreeLevelForm::class, ['model' => $degreeLevel])->renderForm();
    }

    public function update(int|string $id, DegreeLevelRequest $request, BaseHttpResponse $response)
    {
        $degreeLevel = $this->degreeLevelRepository->findOrFail($id);

        if ($request->input('is_default')) {
            $this->degreeLevelRepository->getModel()->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $degreeLevel->fill($request->input());

        $this->degreeLevelRepository->createOrUpdate($degreeLevel);

        event(new UpdatedContentEvent(DEGREE_LEVEL_MODULE_SCREEN_NAME, $request, $degreeLevel));

        return $response
            ->setPreviousUrl(route('degree-levels.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $degreeLevel = $this->degreeLevelRepository->findOrFail($id);

            $this->degreeLevelRepository->delete($degreeLevel);

            event(new DeletedContentEvent(DEGREE_LEVEL_MODULE_SCREEN_NAME, $request, $degreeLevel));

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
            $degreeLevel = $this->degreeLevelRepository->findOrFail($id);
            $this->degreeLevelRepository->delete($degreeLevel);
            event(new DeletedContentEvent(DEGREE_LEVEL_MODULE_SCREEN_NAME, $request, $degreeLevel));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

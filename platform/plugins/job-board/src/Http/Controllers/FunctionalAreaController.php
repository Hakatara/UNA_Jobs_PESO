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
use Botble\JobBoard\Forms\FunctionalAreaForm;
use Botble\JobBoard\Http\Requests\FunctionalAreaRequest;
use Botble\JobBoard\Repositories\Interfaces\FunctionalAreaInterface;
use Botble\JobBoard\Tables\FunctionalAreaTable;
use Exception;
use Illuminate\Http\Request;

class FunctionalAreaController extends BaseController
{
    public function __construct(protected FunctionalAreaInterface $functionalAreaRepository)
    {
    }

    public function index(FunctionalAreaTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::functional-area.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::functional-area.create'));

        return $formBuilder->create(FunctionalAreaForm::class)->renderForm();
    }

    public function store(FunctionalAreaRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            $this->functionalAreaRepository->getModel()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $functionalArea = $this->functionalAreaRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));

        return $response
            ->setPreviousUrl(route('functional-areas.index'))
            ->setNextUrl(route('functional-areas.edit', $functionalArea->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $functionalArea = $this->functionalAreaRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $functionalArea));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $functionalArea->name]));

        return $formBuilder->create(FunctionalAreaForm::class, ['model' => $functionalArea])->renderForm();
    }

    public function update(int|string $id, FunctionalAreaRequest $request, BaseHttpResponse $response)
    {
        $functionalArea = $this->functionalAreaRepository->findOrFail($id);

        if ($request->input('is_default')) {
            $this->functionalAreaRepository->getModel()->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $functionalArea->fill($request->input());

        $this->functionalAreaRepository->createOrUpdate($functionalArea);

        event(new UpdatedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));

        return $response
            ->setPreviousUrl(route('functional-areas.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $functionalArea = $this->functionalAreaRepository->findOrFail($id);

            $this->functionalAreaRepository->delete($functionalArea);

            event(new DeletedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));

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
            $functionalArea = $this->functionalAreaRepository->findOrFail($id);
            $this->functionalAreaRepository->delete($functionalArea);
            event(new DeletedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

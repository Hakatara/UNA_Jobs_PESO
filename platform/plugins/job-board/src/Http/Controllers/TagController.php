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
use Botble\JobBoard\Forms\TagForm;
use Botble\JobBoard\Http\Requests\TagRequest;
use Botble\JobBoard\Repositories\Interfaces\TagInterface;
use Botble\JobBoard\Tables\TagTable;
use Exception;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    public function __construct(protected TagInterface $tagRepository)
    {
    }

    public function index(TagTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::tag.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::tag.create'));

        return $formBuilder->create(TagForm::class)->renderForm();
    }

    public function store(TagRequest $request, BaseHttpResponse $response)
    {
        $tag = $this->tagRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));

        return $response
            ->setPreviousUrl(route('job-board.tag.index'))
            ->setNextUrl(route('job-board.tag.edit', $tag->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $tag = $this->tagRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $tag));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $tag->name]));

        return $formBuilder->create(TagForm::class, ['model' => $tag])->renderForm();
    }

    public function update(int|string $id, TagRequest $request, BaseHttpResponse $response)
    {
        $tag = $this->tagRepository->findOrFail($id);

        $tag->fill($request->input());

        $this->tagRepository->createOrUpdate($tag);

        event(new UpdatedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));

        return $response
            ->setPreviousUrl(route('job-board.tag.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $tag = $this->tagRepository->findOrFail($id);

            $this->tagRepository->delete($tag);

            event(new DeletedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));

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
            $tag = $this->tagRepository->findOrFail($id);
            $this->tagRepository->delete($tag);
            event(new DeletedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getAllTags()
    {
        return $this->tagRepository->pluck('name');
    }
}

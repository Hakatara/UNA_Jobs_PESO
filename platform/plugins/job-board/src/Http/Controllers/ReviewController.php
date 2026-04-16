<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Repositories\Interfaces\ReviewInterface;
use Botble\JobBoard\Tables\ReviewTable;
use Exception;
use Illuminate\Http\Request;

class ReviewController
{
    public function __construct(protected ReviewInterface $reviewRepository)
    {
    }

    public function index(ReviewTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/job-board::review.name'));

        Assets::addStylesDirectly('vendor/core/plugins/job-board/css/review.css');

        return $dataTable->renderTable();
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $review = $this->reviewRepository->findOrFail($id);
            $this->reviewRepository->delete($review);

            event(new DeletedContentEvent(REVIEW_MODULE_SCREEN_NAME, $request, $review));

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
            $review = $this->reviewRepository->findOrFail($id);
            $this->reviewRepository->delete($review);

            event(new DeletedContentEvent(REVIEW_MODULE_SCREEN_NAME, $request, $review));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}

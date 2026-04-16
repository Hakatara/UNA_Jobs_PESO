<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Http\Requests\AjaxReviewRequest;
use Botble\JobBoard\Http\Requests\StoreReviewRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\Company;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\JobBoard\Repositories\Interfaces\ReviewInterface;
use Illuminate\Support\Facades\Auth;

class ReviewController
{
    public function __construct(
        protected ReviewInterface $reviewRepository,
        protected CompanyInterface $companyRepository,
        protected AccountInterface $accountRepository
    ) {
        if (! JobBoardHelper::isEnabledReview()) {
            abort(404);
        }
    }

    public function store(StoreReviewRequest $request, BaseHttpResponse $response)
    {
        $account = Auth::guard('account')->user();

        $reviewable = match ($request->input('reviewable_type')) {
            Company::class => $this->companyRepository->findOrFail($request->input('reviewable_id')),
            Account::class => $this->accountRepository->findOrFail($request->input('reviewable_id')),
            default => null,
        };

        if (! $reviewable || ! $account->canReview($reviewable)) {
            return $response->setError()
                ->setMessage(__('You can not review.'));
        }

        $formData = [
            'reviewable_type' => $request->input('reviewable_type'),
            'reviewable_id' => $request->input('reviewable_id'),
            'created_by_type' => $account->isJobSeeker() ? Account::class : Company::class,
            'created_by_id' => $account->isJobSeeker() ? $account->getKey() : $request->input('company_id'),
        ];

        if ($this->reviewRepository->getFirstBy($formData)) {
            return $response->setError()
                ->setMessage(__('You have already reviewed.'));
        }

        $this->reviewRepository->create(array_merge($formData, [
            'star' => $request->input('star'),
            'review' => $request->input('review'),
        ]));

        return $response->setMessage(__('Added review successfully!'));
    }

    public function loadMore(AjaxReviewRequest $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $reviewable = match ($request->input('reviewable_type')) {
            Company::class => $this->companyRepository->findOrFail($request->input('reviewable_id')),
            Account::class => $this->accountRepository->findOrFail($request->input('reviewable_id')),
            default => null,
        };

        if (! $reviewable) {
            abort(404);
        }

        $reviews = $this->reviewRepository->getModel()
            ->where('reviewable_type', $request->input('reviewable_type'))
            ->where('reviewable_id', $request->input('reviewable_id'))
            ->latest()
            ->paginate(10);

        return JobBoardHelper::view('partials.review-load', compact('reviews'))->render();
    }
}

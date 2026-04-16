<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\JobApplicationInterface;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountDownloadCvController extends BaseController
{
    public function __invoke(
        string|null $slug,
        Request $request,
        JobApplicationInterface $jobApplication,
        AccountInterface $accountRepository
    ): StreamedResponse {
        if (! $slug) {
            abort(404);
        }

        $slug = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Account::class));

        if (! $slug) {
            abort(404);
        }

        $condition = [
            ['id', '=', $slug->reference_id],
            ['type', '=', AccountTypeEnum::JOB_SEEKER],
        ];

        if (setting('verify_account_email', 0)) {
            $condition[] = ['confirmed_at', '!=', null];
        }

        $candidate = $accountRepository->getModel()
            ->where($condition)
            ->firstOrFail();

        $candidate->setRelation('slugable', $slug);

        if (
            $candidate->hide_cv ||
            ! $candidate->resume ||
            $candidate->resume !== $request->input('path') ||
            ! $candidate->isJobSeeker()
        ) {
            abort(404);
        }

        return Storage::download($candidate->resume);
    }
}

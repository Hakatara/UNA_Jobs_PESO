<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\AccountExperienceForm;
use Botble\JobBoard\Http\Requests\AccountExperienceRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\AccountExperience;

class AccountExperienceController extends BaseController
{
    public function create()
    {
        $account = auth('account')->user();

        return JobBoardHelper::scope('account.experiences.create', compact('account'));
    }

    public function store(AccountExperienceRequest $request, BaseHttpResponse $response)
    {
        $account = Account::findOrFail($request->input('account_id'));

        if ($account->isJobSeeker()) {
            AccountExperience::create(array_merge(
                $request->validated(),
                ['account_id' => $account->id]
            ));
        }

        return $response
            ->setNextUrl(route('public.account.experiences.index'))
            ->setMessage(trans('plugins/job-board::account.experiences.store'));
    }

    public function update(AccountExperienceRequest $request, $id, BaseHttpResponse $response)
    {
        $experience = AccountExperience::query()
            ->where('id', $id)
            ->where('account_id', $request->input('account_id'))
            ->firstOrFail();

        $experience->update($request->validated());

        return $response
            ->setNextUrl(route('public.account.experiences.index'))
            ->setMessage(trans('plugins/job-board::account.experiences.update'));
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        $experience = AccountExperience::findOrFail($id);

        $experience->delete();

        return $response
            ->setNextUrl(route('public.account.experiences.index'))
            ->setMessage(trans('plugins/job-board::account.experiences.delete'));
    }

    public function editModal($id, $accountId, FormBuilder $formBuilder)
    {
        $experience = AccountExperience::query()
            ->where('account_id', $accountId)
            ->where('id', $id)
            ->firstOrFail();

        return $formBuilder->create(AccountExperienceForm::class, ['model' => $experience])->setFormOptions([
            'url' => route('accounts.experiences.edit.update', $id),
        ])->renderForm();
    }
}

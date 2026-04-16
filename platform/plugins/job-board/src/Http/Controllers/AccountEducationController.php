<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\AccountEducationForm;
use Botble\JobBoard\Http\Requests\AccountEducationRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\AccountEducation;

class AccountEducationController extends BaseController
{
    public function create()
    {
        $account = auth('account')->user();

        return JobBoardHelper::scope('account.educations.create', compact('account'));
    }

    public function store(AccountEducationRequest $request, BaseHttpResponse $response)
    {
        $account = Account::findOrFail($request->input('account_id'));

        if ($account->isJobSeeker()) {
            AccountEducation::create(array_merge(
                $request->validated(),
                ['account_id' => $account->id]
            ));
        }

        return $response
            ->setNextUrl(route('public.account.educations.index'))
            ->setMessage(trans('plugins/job-board::account.educations.store'));
    }

    public function update(AccountEducationRequest $request, $id, BaseHttpResponse $response)
    {
        $education = AccountEducation::query()
            ->where('id', $id)
            ->where('account_id', $request->input('account_id'))
            ->firstOrFail();

        $education->update($request->validated());

        return $response
            ->setNextUrl(route('public.account.educations.index'))
            ->setMessage(trans('plugins/job-board::account.educations.update'));
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        $education = AccountEducation::findOrFail($id);

        $education->delete();

        return $response
            ->setNextUrl(route('public.account.educations.index'))
            ->setMessage(trans('plugins/job-board::account.educations.delete'));
    }

    public function editModal($id, $accountId, FormBuilder $formBuilder)
    {
        $education = AccountEducation::query()
            ->where('account_id', $accountId)
            ->where('id', $id)
            ->firstOrFail();

        return $formBuilder->create(AccountEducationForm::class, ['model' => $education])->setFormOptions([
            'url' => route('accounts.educations.edit.update', $id),
        ])->renderForm();
    }
}

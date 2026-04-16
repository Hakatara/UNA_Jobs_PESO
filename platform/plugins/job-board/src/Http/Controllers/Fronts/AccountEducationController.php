<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

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
    public function index()
    {
        $account = auth('account')->user();
        $educations = AccountEducation::where('account_id', $account->id)->get();

        return JobBoardHelper::scope('account.educations.index', compact('account', 'educations'));
    }

    public function create()
    {
        $account = auth('account')->user();

        return JobBoardHelper::scope('account.educations.create', compact('account'));
    }

    public function store(AccountEducationRequest $request, BaseHttpResponse $response)
    {
        $account = Account::findOrFail(auth('account')->id());

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

    public function edit($id)
    {
        $account = auth('account')->user();
        $education = AccountEducation::query()
            ->where('account_id', $account->id)
            ->where('id', $id)
            ->firstOrFail();

        return JobBoardHelper::scope('account.educations.edit', compact('account', 'education'));
    }

    public function update(AccountEducationRequest $request, $id, BaseHttpResponse $response)
    {
        $education = AccountEducation::query()
            ->where('id', $id)
            ->where('account_id', auth('account')->id())
            ->firstOrFail();

        $education->update($request->validated());

        return $response
            ->setNextUrl(route('public.account.educations.index'))
            ->setMessage(trans('plugins/job-board::account.educations.update'));
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        $education = AccountEducation::query()
            ->where('id', $id)
            ->where('account_id', auth('account')->id())
            ->firstOrFail();

        $education->delete();

        return $response
            ->setNextUrl(route('public.account.educations.index'))
            ->setMessage(trans('plugins/job-board::account.educations.delete'));
    }

    public function editModal($id, FormBuilder $formBuilder)
    {
        $education = AccountEducation::findOrFail($id);

        return $formBuilder->create(AccountEducationForm::class, ['model' => $education])->renderForm();
    }
}

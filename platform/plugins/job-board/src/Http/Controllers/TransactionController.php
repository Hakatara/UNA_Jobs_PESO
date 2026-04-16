<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Http\Requests\CreateTransactionRequest;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\TransactionInterface;
use Illuminate\Support\Facades\Auth;

class TransactionController extends BaseController
{
    public function __construct(
        protected TransactionInterface $transactionRepository,
        protected AccountInterface $accountRepository
    ) {
    }

    public function postCreate($id, CreateTransactionRequest $request, BaseHttpResponse $response)
    {
        $account = $this->accountRepository->findOrFail($id);

        $request->merge([
            'user_id' => Auth::user()->getKey(),
            'account_id' => $id,
        ]);

        $this->transactionRepository->createOrUpdate($request->input());

        $account->credits += $request->input('credits');
        $this->accountRepository->createOrUpdate($account);

        return $response
            ->setMessage(trans('core/base::notices.create_success_message'));
    }
}

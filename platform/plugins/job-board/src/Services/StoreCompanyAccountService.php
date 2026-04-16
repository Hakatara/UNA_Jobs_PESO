<?php

namespace Botble\JobBoard\Services;

use Botble\JobBoard\Models\Company;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreCompanyAccountService
{
    public function execute(Request $request, Company $company): void
    {
        $accounts = DB::table('jb_companies_accounts')
            ->where('jb_companies_accounts.company_id', $company->id)
            ->join('jb_accounts', 'jb_accounts.id', '=', 'jb_companies_accounts.company_id')
            ->select(DB::raw('CONCAT(jb_accounts.first_name, " ", jb_accounts.last_name) as name'))
            ->pluck('name')
            ->all();

        $accountsInput = collect(json_decode($request->input('accounts'), true))->pluck('value')->all();

        if (count($accounts) != count($accountsInput) || count(array_diff($accounts, $accountsInput)) > 0) {
            DB::table('jb_companies_accounts')
                ->where('company_id', $company->id)
                ->delete();

            foreach ($accountsInput as $accountName) {
                if (! trim($accountName)) {
                    continue;
                }

                $account = app(AccountInterface::class)
                    ->getModel()
                    ->where(DB::raw('CONCAT(jb_accounts.first_name, " ", jb_accounts.last_name)'), $accountName)
                    ->first();

                if (! empty($account)) {
                    DB::table('jb_companies_accounts')
                        ->where('jb_companies_accounts.company_id', $company->id)
                        ->insert([
                            'company_id' => $company->id,
                            'account_id' => $account->id,
                        ]);
                }
            }
        }
    }
}

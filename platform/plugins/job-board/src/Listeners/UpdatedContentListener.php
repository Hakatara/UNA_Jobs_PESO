<?php

namespace Botble\JobBoard\Listeners;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\AccountActivityLogInterface;
use Exception;

class UpdatedContentListener
{
    public function handle(UpdatedContentEvent $event): void
    {
        try {
            if ($event->data->id &&
                $event->data->author_type === Account::class &&
                auth('account')->check() &&
                $event->data->author_id == auth('account')->user()->getAuthIdentifier()
            ) {
                app(AccountActivityLogInterface::class)->createOrUpdate([
                    'action' => 'your_job_updated_by_admin',
                    'reference_name' => $event->data->name,
                    'reference_url' => route('public.account.jobs.edit', $event->data->id),
                ]);
            }
        } catch (Exception $exception) {
            info($exception->getMessage());
        }
    }
}

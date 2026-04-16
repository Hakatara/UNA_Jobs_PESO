<?php

namespace Botble\JobBoard\Listeners;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\JobSkillInterface;
use Botble\JobBoard\Repositories\Interfaces\TagInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaveFavoriteTagAndSkillsListener
{
    public function handle(UpdatedContentEvent $event): void
    {
        /**
         * @var Account $account
         */
        $account = $event->data;
        $request = $event->request;

        try {
            DB::beginTransaction();

            if ($request->has('favorite_tags')) {
                $tags = $account->favoriteTags->pluck('name')->all();

                $tagsInput = collect(explode(',', (string)$request->input('favorite_tags')))->all();

                if (count($tags) != count($tagsInput) || count(array_diff($tags, $tagsInput)) > 0) {
                    $account->favoriteTags()->detach();

                    $tags = app(TagInterface::class)->getByWhereIn('id', $tagsInput);

                    if (count($tags)) {
                        $account->favoriteTags()->sync($tagsInput);
                    }
                }
            }

            if ($request->has('favorite_skills')) {
                $skills = $account->favoriteSkills->pluck('name')->all();

                $skillsInput = collect(explode(',', (string)$request->input('favorite_skills')))->all();

                if (count($skills) != count($skillsInput) || count(array_diff($skills, $skillsInput)) > 0) {
                    $account->favoriteSkills()->detach();

                    $skills = app(JobSkillInterface::class)->getByWhereIn('id', $skillsInput);

                    if (count($skills)) {
                        $account->favoriteSkills()->sync($skillsInput);
                    }
                }
            }
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);
        }
    }
}

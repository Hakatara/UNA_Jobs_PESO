<?php

namespace Botble\JobBoard\Services;

use Botble\Base\Events\CreatedContentEvent;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Repositories\Interfaces\TagInterface;
use Illuminate\Http\Request;

class StoreTagService
{
    public function __construct(protected TagInterface $tagRepository)
    {
    }

    public function execute(Request $request, Job $job): void
    {
        $tags = $job->tags->pluck('name')->all();

        $tagsInput = collect(json_decode((string)$request->input('tag'), true))->pluck('value')->all();

        if (count($tags) != count($tagsInput) || count(array_diff($tags, $tagsInput)) > 0) {
            $job->tags()->detach();
            foreach ($tagsInput as $tagName) {
                if (! trim($tagName)) {
                    continue;
                }

                $tag = $this->tagRepository->getFirstBy(['name' => $tagName]);

                if ($tag === null && ! empty($tagName)) {
                    $tag = $this->tagRepository->createOrUpdate(['name' => $tagName]);

                    $request->merge(['slug' => $tagName]);

                    event(new CreatedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));
                }

                if (! empty($tag)) {
                    $job->tags()->attach($tag->id);
                }
            }
        }
    }
}

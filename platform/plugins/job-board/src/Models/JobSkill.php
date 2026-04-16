<?php

namespace Botble\JobBoard\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobSkill extends BaseModel
{
    protected $table = 'jb_job_skills';

    protected $fillable = [
        'name',
        'order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'jb_jobs_skills', 'job_skill_id', 'job_id', );
    }
}

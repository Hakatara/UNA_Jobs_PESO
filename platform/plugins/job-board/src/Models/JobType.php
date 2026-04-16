<?php

namespace Botble\JobBoard\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobType extends BaseModel
{
    protected $table = 'jb_job_types';

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
        return $this->belongsToMany(Job::class, 'jb_jobs_types', 'job_type_id', 'job_id');
    }
}

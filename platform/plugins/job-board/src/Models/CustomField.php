<?php

namespace Botble\JobBoard\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\JobBoard\Enums\CustomFieldEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomField extends BaseModel
{
    protected $table = 'jb_custom_fields';

    protected $fillable = [
        'name',
        'type',
        'order',
        'is_global',
        'authorable_type',
        'authorable_id',
    ];

    protected $casts = [
        'type' => CustomFieldEnum::class,
        'is_global' => 'bool',
        'order' => 'int',
        'name' => SafeContent::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (CustomField $customField) {
            $customField->options()->delete();
            $customField->customFieldValue()->delete();
        });
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function options(): HasMany
    {
        return $this->hasMany(CustomFieldOption::class, 'custom_field_id');
    }

    public function customFieldValue(): HasOne
    {
        return $this->hasOne(CustomFieldValue::class);
    }
}

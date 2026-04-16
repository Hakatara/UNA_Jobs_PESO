<?php

namespace Botble\JobBoard\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static JobStatusEnum DRAFT()
 * @method static JobStatusEnum PUBLISHED()
 * @method static JobStatusEnum PENDING()
 * @method static JobStatusEnum CLOSED()
 */
class JobStatusEnum extends Enum
{
    public const PUBLISHED = 'published';
    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const CLOSED = 'closed';

    public static $langPath = 'plugins/job-board::job.statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::DRAFT => Html::tag('span', self::DRAFT()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::CLOSED => Html::tag('span', self::CLOSED()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::PUBLISHED => Html::tag('span', self::PUBLISHED()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}

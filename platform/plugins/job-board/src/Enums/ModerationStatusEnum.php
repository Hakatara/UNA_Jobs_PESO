<?php

namespace Botble\JobBoard\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static ModerationStatusEnum APPROVED()
 * @method static ModerationStatusEnum PENDING()
 * @method static ModerationStatusEnum REJECTED()
 */
class ModerationStatusEnum extends Enum
{
    public const APPROVED = 'approved';
    public const PENDING = 'pending';
    public const REJECTED = 'rejected';

    public static $langPath = 'plugins/job-board::job.moderation_statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::APPROVED => Html::tag('span', self::APPROVED()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::REJECTED => Html::tag('span', self::REJECTED()->label(), ['class' => 'label-danger status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}

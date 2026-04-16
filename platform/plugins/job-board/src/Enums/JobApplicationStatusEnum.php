<?php

namespace Botble\JobBoard\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static JobApplicationStatusEnum PENDING()
 * @method static JobApplicationStatusEnum CHECKED()
 */
class JobApplicationStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const CHECKED = 'checked';

    public static $langPath = 'plugins/job-board::job-application.statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::CHECKED => Html::tag('span', self::CHECKED()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}

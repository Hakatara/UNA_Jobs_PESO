<?php

namespace Botble\JobBoard\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static SalaryRangeEnum MONTHLY()
 * @method static SalaryRangeEnum YEARLY()
 * @method static SalaryRangeEnum HOURLY()
 * @method static SalaryRangeEnum WEEKLY()
 */
class SalaryRangeEnum extends Enum
{
    public const MONTHLY = 'monthly';
    public const YEARLY = 'yearly';
    public const WEEKLY = 'weekly';
    public const DAILY = 'daily';
    public const HOURLY = 'hourly';

    public static $langPath = 'plugins/job-board::job.salary_periods';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::HOURLY, self::DAILY => Html::tag('span', self::HOURLY()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::WEEKLY => Html::tag('span', self::WEEKLY()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::MONTHLY => Html::tag('span', self::MONTHLY()->label(), ['class' => 'label-primary status-label'])
                ->toHtml(),
            self::YEARLY => Html::tag('span', self::YEARLY()->label(), ['class' => 'label-primary status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}

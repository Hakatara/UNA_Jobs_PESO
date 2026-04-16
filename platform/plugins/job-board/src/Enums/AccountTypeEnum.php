<?php

namespace Botble\JobBoard\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static AccountTypeEnum JOB_SEEKER()
 * @method static AccountTypeEnum EMPLOYER()
 */
class AccountTypeEnum extends Enum
{
    public const JOB_SEEKER = 'job-seeker';
    public const EMPLOYER = 'employer';

    public static $langPath = 'plugins/job-board::account.types';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::JOB_SEEKER => Html::tag('span', self::JOB_SEEKER()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::EMPLOYER => Html::tag('span', self::EMPLOYER()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}

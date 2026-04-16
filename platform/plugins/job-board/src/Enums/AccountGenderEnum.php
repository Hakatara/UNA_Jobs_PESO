<?php

namespace Botble\JobBoard\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static AccountGenderEnum MALE()
 * @method static AccountGenderEnum FEMALE()
 * @method static AccountGenderEnum OTHER()
 */
class AccountGenderEnum extends Enum
{
    public const MALE   = 'male';
    public const FEMALE = 'female';
    public const OTHER  = 'other';

    public static $langPath = 'plugins/job-board::account.enums.genders';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::MALE => Html::tag('span', self::MALE()->label(), ['class' => 'text-info'])
                ->toHtml(),
            self::FEMALE => Html::tag('span', self::FEMALE()->label(), ['class' => 'text-success'])
                ->toHtml(),
            self::OTHER => Html::tag('span', self::OTHER()->label(), ['class' => 'text-primary'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}

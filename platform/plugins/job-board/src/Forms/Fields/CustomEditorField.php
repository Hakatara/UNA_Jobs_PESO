<?php

namespace Botble\JobBoard\Forms\Fields;

use Botble\Base\Forms\FormField;
use Botble\JobBoard\Facades\JobBoardHelper;

class CustomEditorField extends FormField
{
    protected function getTemplate(): string
    {
        return JobBoardHelper::viewPath('dashboard.forms.fields.custom-editor');
    }
}

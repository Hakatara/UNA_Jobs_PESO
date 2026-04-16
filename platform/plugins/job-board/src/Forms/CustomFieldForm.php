<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Enums\CustomFieldEnum;
use Botble\JobBoard\Http\Requests\CustomFieldRequest;
use Botble\JobBoard\Models\CustomField;

class CustomFieldForm extends FormAbstract
{
    public function buildForm(): void
    {
        Assets::addScripts(['jquery-ui'])
            ->addScriptsDirectly([
                'vendor/core/plugins/job-board/js/global-custom-fields.js',
            ]);

        $this
            ->setupModel(new CustomField())
            ->setValidatorClass(CustomFieldRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('type', 'customSelect', [
                'label' => trans('plugins/job-board::custom-fields.type'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => ['class' => 'form-control custom-field-type'],
                'choices' => CustomFieldEnum::labels(),
            ])
            ->setBreakFieldPoint('type')
            ->addMetaBoxes([
                'custom_fields_box' => [
                    'attributes' => [
                        'id' => 'custom_fields_box',
                    ],
                    'id' => 'custom_fields_box',
                    'title' => trans('plugins/job-board::custom-fields.options'),
                    'content' => view(
                        'plugins/job-board::custom-fields.options',
                        ['options' => $this->model->options->sortBy('order')]
                    )->render(),
                ],
            ]);
    }
}

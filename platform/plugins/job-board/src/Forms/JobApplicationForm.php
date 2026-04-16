<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Enums\JobApplicationStatusEnum;
use Botble\JobBoard\Http\Requests\EditJobApplicationRequest;
use Botble\JobBoard\Models\JobApplication;

class JobApplicationForm extends FormAbstract
{
    public function buildForm(): void
    {
        Assets::addStylesDirectly('vendor/core/plugins/job-board/css/application.css');

        $this
            ->setupModel(new JobApplication())
            ->withCustomFields()
            ->setValidatorClass(EditJobApplicationRequest::class)
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => JobApplicationStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status')
            ->addMetaBoxes([
                'information' => [
                    'title' => trans('plugins/job-board::job-application.information'),
                    'content' => view('plugins/job-board::info', ['jobApplication' => $this->getModel()])->render(),
                    'attributes' => [
                        'style' => 'margin-top: 0',
                    ],
                ],
            ]);
    }
}

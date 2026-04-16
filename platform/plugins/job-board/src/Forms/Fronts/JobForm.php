<?php

namespace Botble\JobBoard\Forms\Fronts;

use Botble\Base\Facades\Assets;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\Fields\CustomEditorField;
use Botble\JobBoard\Forms\JobForm as FormsJobForm;
use Botble\JobBoard\Http\Requests\AccountJobRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\Job;

class JobForm extends FormsJobForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        if (! $this->formHelper->hasCustomField('customEditor')) {
            Assets::addScriptsDirectly('vendor/core/core/base/libraries/tinymce/tinymce.min.js');
            $this->formHelper->addCustomField('customEditor', CustomEditorField::class);
        }

        Assets::addScriptsDirectly('vendor/core/plugins/job-board/js/employer-colleagues.js');

        Assets::usingVueJS();

        /**
         * @var Account $account
         */
        $account = auth('account')->user();
        $companies = $account->companies->pluck('name', 'id')->all();

        $this
            ->setupModel(new Job())
            ->setFormOption('template', JobBoardHelper::viewPath('dashboard.forms.base'))
            ->setFormOption('enctype', 'multipart/form-data')
            ->setValidatorClass(AccountJobRequest::class)
            ->setActionButtons(view('plugins/job-board::themes.dashboard.forms.actions')->render())
            ->remove('is_featured')
            ->remove('moderation_status')
            ->remove('content')
            ->remove('company_id')
            ->remove('never_expired')
            ->removeMetaBox('image')
            
            ->addBefore('number_of_positions', 'company_id', 'customSelect', [
                'label' => __('Company'),
                'label_attr' => ['class' => 'control-label required'],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'choices' => $companies,
            ])
            ->addAfter('description', 'content', 'customEditor', [
                'label' => trans('core/base::forms.content'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'model' => Job::class,
                ],
            ])
            ->modify('tag', 'tags', [
                'attr' => [
                    'placeholder' => trans('plugins/job-board::job.write_some_tags'),
                    'data-url' => route('public.account.jobs.tags.all'),
                ],
            ]);
    }
}

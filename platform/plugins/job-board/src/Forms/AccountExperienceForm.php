<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Http\Requests\AccountExperienceRequest;
use Botble\JobBoard\Models\AccountExperience;

class AccountExperienceForm extends FormAbstract
{
    protected $template = 'core/base::forms.form-content-only';

    public function buildForm(): void
    {
        $data = $this->getData();

        $this
            ->setupModel(new AccountExperience())
            ->setValidatorClass(AccountExperienceRequest::class)
            ->withCustomFields()
            ->setFormOptions([
                'url' => route('accounts.experiences.create.store'),
            ])
            ->add('company', 'text', [
                'label' => trans('plugins/job-board::account.form.company'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/job-board::account.form.company_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('account_id', 'hidden', [
                'label' => 'Account',
                'label_attr' => ['class' => 'control-label required'],
                'value' => $data['account']->id ?? $this->getModel()->account_id,
            ])
            ->add('position', 'text', [
                'label' => trans('plugins/job-board::account.form.position'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/job-board::account.form.position_placeholder'),
                ],
            ])
            ->add('started_at', 'date', [
                'label' => trans('plugins/job-board::account.form.started_at'),
                'label_attr' => ['class' => 'control-label required'],
                'value' => $this->getModel()->id ? BaseHelper::formatDate($this->getModel()->started_at) : '',
            ])
            ->add('ended_at', 'date', [
                'label' => trans('plugins/job-board::account.form.ended_at'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $this->getModel()->id ? BaseHelper::formatDate($this->getModel()->ended_at) : '',
            ])
            ->add('description', 'textarea', [
                'label' => trans('plugins/job-board::account.form.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 3,
                ],
            ])
            ->setBreakFieldPoint('status');
    }
}

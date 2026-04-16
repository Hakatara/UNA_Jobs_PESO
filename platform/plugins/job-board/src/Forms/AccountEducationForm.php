<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Http\Requests\AccountEducationRequest;
use Botble\JobBoard\Models\AccountEducation;

class AccountEducationForm extends FormAbstract
{
    protected $template = 'core/base::forms.form-content-only';

    public function buildForm(): void
    {
        $data = $this->getData();

        $this
            ->setupModel(new AccountEducation())
            ->setValidatorClass(AccountEducationRequest::class)
            ->withCustomFields()
            ->setFormOptions([
                'url' => route('accounts.educations.create.store'),
            ])
            ->add('school', 'text', [
                'label' => trans('plugins/job-board::account.form.school'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/job-board::account.form.school_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('account_id', 'hidden', [
                'label' => 'account',
                'label_attr' => ['class' => 'control-label required'],
                'value' => $data['account']->id ?? $this->getModel()->account_id,
            ])
            ->add('specialized', 'text', [
                'label' => trans('plugins/job-board::account.form.specialized'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/job-board::account.form.specialized_placeholder'),
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

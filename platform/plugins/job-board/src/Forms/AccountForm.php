<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Enums\AccountGenderEnum;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Http\Requests\AccountCreateRequest;
use Botble\JobBoard\Models\Account;

class AccountForm extends FormAbstract
{
    protected $template = 'plugins/job-board::accounts.form';

    public function buildForm(): void
    {
        Assets::addStylesDirectly('vendor/core/plugins/job-board/css/account-admin.css')
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/account-admin.js');

        $this
            ->setupModel(new Account())
            ->setValidatorClass(AccountCreateRequest::class)
            ->withCustomFields()
            ->add('first_name', 'text', [
                'label' => __('First Name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('last_name', 'text', [
                'label' => __('Last Name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'text', [
                'label' => trans('plugins/job-board::account.form.email'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __('Ex: example@gmail.com'),
                    'data-counter' => 60,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 400,
                ],
            ])
            ->add('bio', 'editor', [
                'label' => __('Bio'),
                'label_attr' => ['class' => 'control-label'],
            ]);

        if (is_plugin_active('location')) {
            $this->add('location', 'selectLocation', [
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group mb-0 col-sm-4',
                ],
                'wrapperClassName' => 'row g-1',
            ]);
        }

        $this
            ->add('address', 'text', [
                'label' => __('Address'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('phone', 'text', [
                'label' => __('Phone'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => __('Phone'),
                    'data-counter' => 20,
                ],
            ])
            ->add('dob', 'datePicker', [
                'label' => __('Date of birth'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $this->getModel()->id ? BaseHelper::formatDate($this->getModel()->dob) : '',
            ])
            ->add('is_change_password', 'checkbox', [
                'label' => trans('plugins/job-board::account.form.change_password'),
                'label_attr' => ['class' => 'control-label'],
                'value' => 1,
            ])
            ->add('password', 'password', [
                'label' => trans('plugins/job-board::account.form.password'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 60,
                ],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($this->getModel()->id ? ' hidden' : null),
                ],
            ])
            ->add('password_confirmation', 'password', [
                'label' => trans('plugins/job-board::account.form.password_confirmation'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 60,
                ],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($this->getModel()->id ? ' hidden' : null),
                ],
            ])
            ->add('type', 'customSelect', [
                'label' => trans('plugins/job-board::account.type'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => AccountTypeEnum::labels(),
            ])
            ->add('available_for_hiring', 'onOff', [
                'label' => trans('plugins/job-board::account.form.available_for_hiring'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->add('is_public_profile', 'onOff', [
                'label' => trans('plugins/job-board::account.form.is_public_profile'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->add('hide_cv', 'onOff', [
                'label' => trans('plugins/job-board::account.form.hide_cv'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->setBreakFieldPoint('type')
            ->add('is_featured', 'onOff', [
                'label' => trans('core/base::forms.is_featured'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('gender', 'customSelect', [
                'label' => trans('plugins/job-board::account.form.gender'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => AccountGenderEnum::labels(),
                'empty_value' => __('-- select --'),
            ])
            ->add('avatar_image', 'mediaImage', [
                'label' => trans('plugins/job-board::account.form.avatar_image'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $this->getModel()->avatar->url,
            ])
            ->add('resume', 'mediaFile', [
                'label' => trans('plugins/job-board::account.form.resume'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $this->getModel()->resume,
            ]);

        if ($this->model->id && $this->model->isJobSeeker()) {
            $this->addMetaBoxes([
                'educations' => [
                    'title' => null,
                    'content' => view('plugins/job-board::accounts.educations', [
                        'educations' => $this->model->educations()->get(),
                    ])->render(),
                    'wrap' => false,
                ],
            ]);

            $this->addMetaBoxes([
                'experiences' => [
                    'title' => null,
                    'content' => view('plugins/job-board::accounts.experiences', [
                        'experiences' => $this->model->experiences()->get(),
                    ])->render(),
                    'wrap' => false,
                ],
            ]);
        }
    }
}

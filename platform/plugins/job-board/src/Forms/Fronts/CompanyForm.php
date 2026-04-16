<?php

namespace Botble\JobBoard\Forms\Fronts;

use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\Fields\CustomEditorField;
use Botble\JobBoard\Forms\Fields\CustomImageField;
use Botble\JobBoard\Http\Requests\CompanyRequest;
use Botble\JobBoard\Models\Company;

class CompanyForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Company())
            ->setValidatorClass(CompanyRequest::class)
            ->withCustomFields()
            ->setFormOption('enctype', 'multipart/form-data')
            ->addCustomField('customEditor', CustomEditorField::class)
            ->addCustomField('customImage', CustomImageField::class)
            ->setActionButtons(view('plugins/job-board::themes.dashboard.forms.actions')->render())
            ->setFormOption('template', JobBoardHelper::viewPath('dashboard.forms.base'))
            ->add('name', 'text', [
                'label' => __('Company Name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 350,
                ],
            ])
            ->add('content', 'customEditor', [
                'label' => trans('core/base::forms.content'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                ],
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('ceo', 'text', [
                'label' => __('Company CEO / President'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Company CEO / President'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'email', [
                'label' => __('Email'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: contact@your-company.com'),
                    'data-counter' => 120,
                ],
            ])
            ->add('phone', 'text', [
                'label' => __('Phone'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Phone number, will be public'),
                    'data-counter' => 30,
                ],
            ])
            ->add('website', 'text', [
                'label' => __('Website'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('https://'),
                    'data-counter' => 120,
                ],
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('year_founded', 'number', [
                'label' => __('Year founded'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 1987'),
                    'data-counter' => 10,
                ],
            ])
            ->add('number_of_offices', 'number', [
                'label' => __('Number of offices'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 3'),
                    'data-counter' => 10,
                ],
            ])
            ->add('number_of_employees', 'number', [
                'label' => __('Number of employees'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 100-250'),
                    'data-counter' => 10,
                ],
            ])
            
            ->add('rowClose2', 'html', [
                'html' => '</div>',
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
            ->add('rowOpen3', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('address', 'text', [
                'label' => __('Address'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'placeholder' => __('Address'),
                    'data-counter' => 120,
                ],
            ])
            ->add('postal_code', 'text', [
                'label' => __('Postal code'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'placeholder' => __('Postal code'),
                    'data-counter' => 20,
                ],
            ])
            ->add('rowClose3', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen4', 'html', [
                'html' => '<div class="row">',
            ])
            
            ->add('rowClose4', 'html', [
                'html' => '</div>',
            ])
            ->add('logo', 'customImage', [
                'label' => __('Logo (~500x500)'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->add('cover_image', 'customImage', [
                'label' => __('Cover Image (~1920x300)'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->setBreakFieldPoint('logo')
            ->addMetaBoxes([
                'social_links' => [
                    'title' => __('Social links'),
                    'content' => view(
                        JobBoardHelper::viewPath('dashboard.forms.social-links'),
                        ['company' => $this->getModel()]
                    )->render(),
                ],
            ]);
    }
}

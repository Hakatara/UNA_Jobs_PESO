<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\TagField;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Enums\CustomFieldEnum;
use Botble\JobBoard\Enums\JobStatusEnum;
use Botble\JobBoard\Enums\ModerationStatusEnum;
use Botble\JobBoard\Enums\SalaryRangeEnum;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Http\Requests\JobRequest;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Repositories\Interfaces\CareerLevelInterface;
use Botble\JobBoard\Repositories\Interfaces\CategoryInterface;
use Botble\JobBoard\Repositories\Interfaces\CurrencyInterface;
use Botble\JobBoard\Repositories\Interfaces\CustomFieldInterface;
use Botble\JobBoard\Repositories\Interfaces\DegreeLevelInterface;
use Botble\JobBoard\Repositories\Interfaces\FunctionalAreaInterface;
use Botble\JobBoard\Repositories\Interfaces\JobExperienceInterface;
use Botble\JobBoard\Repositories\Interfaces\JobSkillInterface;
use Botble\JobBoard\Repositories\Interfaces\JobTypeInterface;

class JobForm extends FormAbstract
{
    public function __construct(
        protected JobSkillInterface $jobSkillRepository,
        protected CareerLevelInterface $careerLevelRepository,
        protected CurrencyInterface $currencyRepository,
        protected DegreeLevelInterface $degreeLevelRepository,
        protected JobTypeInterface $jobTypeRepository,
        protected JobExperienceInterface $jobExperienceRepository,
        protected FunctionalAreaInterface $functionalAreaRepository,
        protected CategoryInterface $categoryRepository,
        protected CustomFieldInterface $customFieldRepository,
    ) {
        parent::__construct();
    }

    public function buildForm(): void
    {
        Assets::addScripts(['input-mask'])
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/components.js')
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/job.js')
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/employer-colleagues.js')
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/custom-fields.js');

        Assets::usingVueJS();

        $skills = $this->jobSkillRepository->pluck('name', 'id');

        $selectedSkills = [];
        if (count($skills) > 0) {
            if ($this->model) {
                $selectedSkills = $this->model->skills()->pluck('job_skill_id')->all();
            }
        }

        $jobTypes = $this->jobTypeRepository->pluck('name', 'id');

        $selectedJobTypes = [];
        if (count($jobTypes) > 0) {
            if ($this->model) {
                $selectedJobTypes = $this->model->jobTypes()->pluck('job_type_id')->all();
            }
        }

        $careerLevels = $this->careerLevelRepository
            ->getModel()
            ->orderBy('jb_career_levels.order')
            ->orderBy('jb_career_levels.name')
            ->pluck('jb_career_levels.name', 'jb_career_levels.id')
            ->all();

        $currencies = $this->currencyRepository
            ->getModel()
            ->orderBy('jb_currencies.order')
            ->orderBy('jb_currencies.title')
            ->pluck('jb_currencies.title', 'jb_currencies.id')
            ->all();

        $degreeLevels = $this->degreeLevelRepository
            ->getModel()
            ->orderBy('jb_degree_levels.order')
            ->orderBy('jb_degree_levels.name')
            ->pluck('jb_degree_levels.name', 'jb_degree_levels.id')
            ->all();

        $jobExperiences = $this->jobExperienceRepository
            ->getModel()
            ->orderBy('jb_job_experiences.order')
            ->orderBy('jb_job_experiences.name')
            ->pluck('jb_job_experiences.name', 'jb_job_experiences.id')
            ->all();

        $functionalArea = $this->functionalAreaRepository
            ->getModel()
            ->orderBy('jb_functional_areas.order')
            ->orderBy('jb_functional_areas.name')
            ->pluck('jb_functional_areas.name', 'jb_functional_areas.id')
            ->all();

        $categories = app(CategoryInterface::class)->pluck('name', 'id');

        $selectedCategories = [];
        if ($this->model) {
            $selectedCategories = $this->model->categories()->pluck('category_id')->all();
        }

        $tags = null;

        if ($this->getModel()) {
            $tags = $this->getModel()->tags()->pluck('name')->implode(',');
        }

        $customFields = app(CustomFieldInterface::class)->select(['name', 'id', 'type'])->get();

        $this
            ->setupModel(new Job())
            ->setValidatorClass(JobRequest::class)
            ->withCustomFields()
            ->addCustomField('tags', TagField::class)
            ->addCustomField('multiCheckList', MultiCheckListField::class)
            ->addCustomField('tags', TagField::class)
            ->add('name', 'text', [
                'label' => __('Job title'),
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
                    'data-counter' => 500,
                ],
            ])
            ->add('is_featured', 'onOff', [
                'label' => trans('core/base::forms.is_featured'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('content', 'editor', [
                'label' => trans('core/base::forms.content'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                ],
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('company_id', 'autocomplete', [
                'label' => __('Company'),
                'label_attr' => [
                    'class' => 'control-label required',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'id' => 'company_id',
                    'data-url' => route('companies.list'),
                ],
                'choices' => $this->getModel()->company_id ?
                    [
                        $this->model->company->id => $this->model->company->name,
                    ]
                    :
                    ['' => __('Select company...')],
                'help_block' => [
                    'text' => __('Not in the list? ') . Html::link(
                        '#',
                        __('Add new'),
                        ['data-bs-toggle' => 'modal', 'data-bs-target' => '#add-company-modal']
                    ),
                ],
            ])
            ->add('number_of_positions', 'number', [
                'label' => __('Number of positions'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'placeholder' => __('Number of positions'),
                ],
                'default_value' => 1,
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ]);

        if (JobBoardHelper::isZipCodeEnabled()) {
            $this->add('zip_code', 'text', [
                'label' => __('Zip code'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => __('Zip code'),
                    'data-counter' => 20,
                ],
            ]);
        }

        $this->add('address', 'text', [
            'label' => __('Address'),
            'label_attr' => ['class' => 'control-label'],
            'attr' => [
                'placeholder' => __('Address'),
                'data-counter' => 120,
            ],
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

        $this->add('rowOpen4', 'html', [
            'html' => '<div class="row">',
        ])
            
            ->add('rowClose4', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('salary_from', 'text', [
                'label' => __('Salary from'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'id' => 'salary-from',
                    'placeholder' => __('Salary from'),
                    'class' => 'form-control input-mask-number',
                ],
            ])
            ->add('salary_to', 'text', [
                'label' => __('Salary to'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'id' => 'salary-to',
                    'placeholder' => __('Salary to'),
                    'class' => 'form-control input-mask-number',
                ],
            ])
            ->add('salary_range', 'customSelect', [
                'label' => __('Salary Range'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'choices' => SalaryRangeEnum::labels(),
            ])
            ->add('currency_id', 'customSelect', [
                'label' => __('Currency'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'choices' => $currencies,
            ])
            ->add('rowClose', 'html', [
                'html' => '</div>',
            ])
            ->add('hide_salary', 'onOff', [
                'label' => __('Hide salary?'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('rowOpen5', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('start_date', 'datePicker', [
                'label' => __('Start date'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'value' => $this->getModel()->id ? BaseHelper::formatDate($this->getModel()->start_date) : '',
            ])
            ->add('application_closing_date', 'datePicker', [
                'label' => __('Application closing date'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'value' => $this->getModel()->id ? BaseHelper::formatDate($this->getModel()->application_closing_date) : '',
            ])
            ->add('rowClose5', 'html', [
                'html' => '</div>',
            ])
            
            
            ->add('never_expired', 'onOff', [
                'label' => __('Never expired?'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => true,
            ])
            
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => JobStatusEnum::labels(),
            ])
            ->add('moderation_status', 'customSelect', [
                'label' => trans('plugins/job-board::job.moderation_status'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => ModerationStatusEnum::labels(),
            ])
            
            ->add('categories[]', 'multiCheckList', [
                'label' => __('Job categories'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $categories,
                'value' => old('categories', $selectedCategories),
            ]);

        if (count($skills) > 0) {
            $this->add('skills[]', 'multiCheckList', [
                'label' => __('Job skills'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $skills,
                'value' => old('skills', $selectedSkills),
            ]);
        }

        if (count($jobTypes) > 0) {
            $this->add('jobTypes[]', 'multiCheckList', [
                'label' => __('Job types'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $jobTypes,
                'value' => old('jobTypes', $selectedJobTypes),
            ]);
        }

        $this
            ->add('career_level_id', 'customSelect', [
                'label' => __('Career level'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [0 => __('-- select --')] + $careerLevels,
            ])
            ->add('functional_area_id', 'customSelect', [
                'label' => __('Functional area'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [0 => __('-- select --')] + $functionalArea,
            ])
            ->add('degree_level_id', 'customSelect', [
                'label' => __('Degree level'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [0 => __('-- select --')] + $degreeLevels,
            ])
            ->add('job_experience_id', 'customSelect', [
                'label' => __('Job experience'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [0 => __('-- select --')] + $jobExperiences,
            ])
            
            ->setBreakFieldPoint('status')
            ->addMetaBoxes([
                'add-company' => [
                    'title' => null,
                    'content' => view('plugins/job-board::partials.add-company', ['model' => $this->getModel()]),
                    'priority' => 0,
                    'attributes' => ['style' => 'display: none'],
                ],
                
            ]);
    }
}

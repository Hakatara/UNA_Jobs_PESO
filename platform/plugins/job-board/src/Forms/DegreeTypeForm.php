<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Http\Requests\DegreeTypeRequest;
use Botble\JobBoard\Models\DegreeType;
use Botble\JobBoard\Repositories\Interfaces\DegreeLevelInterface;

class DegreeTypeForm extends FormAbstract
{
    public function __construct(protected DegreeLevelInterface $degreeLevelRepository)
    {
        parent::__construct();
    }

    public function buildForm(): void
    {
        $degreeLevels = $this->degreeLevelRepository->pluck('jb_degree_levels.name', 'jb_degree_levels.id');

        $this
            ->setupModel(new DegreeType())
            ->setValidatorClass(DegreeTypeRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('degree_level_id', 'customSelect', [
                'label' => trans('plugins/job-board::degree-type.degree-level'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                ],
                'choices' => $degreeLevels,
            ])
            ->add('order', 'number', [
                'label' => trans('core/base::forms.order'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.order_by_placeholder'),
                ],
                'default_value' => 0,
            ])
            ->add('is_default', 'onOff', [
                'label' => trans('core/base::forms.is_default'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}

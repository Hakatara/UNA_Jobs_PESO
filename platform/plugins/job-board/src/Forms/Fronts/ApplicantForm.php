<?php

namespace Botble\JobBoard\Forms\Fronts;

use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\JobApplicationForm;
use Botble\JobBoard\Http\Requests\EditJobApplicationRequest;
use Botble\JobBoard\Models\JobApplication;

class ApplicantForm extends JobApplicationForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        $this
            ->setupModel(new JobApplication())
            ->withCustomFields()
            ->setValidatorClass(EditJobApplicationRequest::class)
            ->setActionButtons(view('plugins/job-board::themes.dashboard.forms.actions')->render())
            ->setFormOption('template', JobBoardHelper::viewPath('dashboard.forms.base'));
    }
}

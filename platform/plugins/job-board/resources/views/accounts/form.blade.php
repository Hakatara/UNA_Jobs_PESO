@extends('core/base::forms.form')
@section('form_end')
    @if ($form->getModel()->id)
        {!! Form::modalAction('add-credit-modal', trans('plugins/job-board::account.add_credit_to_account'), 'info', view('plugins/job-board::accounts.credit-form', ['account' => $form->getModel()])->render(), 'confirm-add-credit-button', trans('plugins/job-board::account.action_table.add'), 'modal-md') !!}
    @endif
    {!! Form::modalAction('add-education-modal', trans('plugins/job-board::account.add_education'), 'info', FormBuilder::create(\Botble\JobBoard\Forms\AccountEducationForm::class, [
    'data' => [
        'account' => $form->getModel(),
    ],
    ])->renderForm(), 'confirm-add-education-button', trans('plugins/job-board::account.action_table.add'), 'modal-md') !!}

    {!! Form::modalAction('add-experience-modal', trans('plugins/job-board::account.add_experience'), 'info', FormBuilder::create(\Botble\JobBoard\Forms\AccountExperienceForm::class, [
    'data' => [
        'account' => $form->getModel(),
    ],
    ])->renderForm(), 'confirm-add-experience-button', trans('plugins/job-board::account.action_table.add'), 'modal-md') !!}

    @include('core/table::partials.modal-item', [
        'type' => 'danger',
        'name' => 'modal-confirm-delete',
        'title' => trans('core/base::tables.confirm_delete'),
        'content' => trans('core/base::tables.confirm_delete_msg'),
        'action_name' => trans('core/base::tables.delete'),
        'action_button_attributes' => [
            'class' => 'delete-crud-entry',
        ],
    ])
@endsection

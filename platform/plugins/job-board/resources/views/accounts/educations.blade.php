<div id="education_wrap" class="widget meta-boxes">
    <div class="widget-title">
        <h4><span>{{ __('Educations') }}</span></h4>
    </div>
    <div class="widget-body" id="education-histories">
        <a href="#" class="btn-trigger-add-education" v-pre="">{{ trans('plugins/job-board::account.add_education') }}</a>
        <div class="comment-log-timeline">
            @if ($educations->count() > 0)
                <div class="column-left-history ps-relative" id="order-history-wrapper">
                    <div class="item-card">
                        <div class="item-card-body ">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ trans('plugins/job-board::account.table.educations.school') }}</th>
                                    <th scope="col">{{ trans('plugins/job-board::account.table.educations.specialized') }}</th>
                                    <th scope="col">{{ trans('plugins/job-board::account.table.started_at') }}</th>
                                    <th scope="col">{{ trans('plugins/job-board::account.table.ended_at') }}</th>
                                    <th scope="col">{{ trans('plugins/job-board::account.table.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($educations as $education)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-start">{{ $education->school }}</td>
                                        <td>{{ $education->specialized }}</td>
                                        <td>{{ $education->started_at->format('Y-m-d') }}</td>
                                        <td>{{ $education->ended_at ? $education->ended_at->format('Y-m-d') : trans('plugins/job-board::account.now') }}</td>
                                        <td class="text-center" style="width: 120px;">
                                            <a href="#" class="btn btn-icon btn-sm btn-warning me-1 btn-trigger-edit-education"
                                               data-bs-toggle="tooltip" data-section="{{ route('accounts.educations.edit-modal', [$education->id, $education->account_id]) }}"
                                               role="button" data-bs-original-title="{{ trans('plugins/job-board::account.action_table.edit') }}"
                                            >
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="#" class="btn btn-icon btn-sm btn-danger deleteDialog" data-bs-toggle="tooltip"
                                               data-section="{{ route('accounts.educations.destroy', $education->id) }}" role="button"
                                               data-bs-original-title="{{ trans('plugins/job-board::account.action_table.delete') }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <p>{{ trans('plugins/job-board::account.no_education') }}</p>
            @endif
        </div>
    </div>
</div>

{!! Form::modalAction('edit-education-modal', trans('plugins/job-board::account.edit_education'), 'info', null, 'confirm-edit-education-button', trans('plugins/job-board::account.action_table.edit'), 'modal-md') !!}

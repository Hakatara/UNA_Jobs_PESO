@if ($jobApplication)
    <p>{{ trans('plugins/job-board::job-application.tables.time') }}: <i>{{ $jobApplication->created_at }}</i></p>
    <p>{{ trans('plugins/job-board::job-application.tables.position') }}:
        <a href="{{ $jobApplication->job->url }}" target="_blank"><i>{{ $jobApplication->job->name }}</i> <i class="fa fa-external-link"></i></a>
    </p>

    @if (!$jobApplication->is_external_apply)
        <p>{{ trans('plugins/job-board::job-application.tables.name') }}:
            @if ($jobApplication->account->id && $jobApplication->account->is_public_profile)
                <a href="{{ $jobApplication->account->url }}" target="_blank">{{ $jobApplication->account->name }} <i class="fa fa-external-link"></i></a>
            @else
                <i>{{ $jobApplication->first_name }} {{ $jobApplication->last_name }}</i>
            @endif
        </p>
    @endif

    @if ($jobApplication->phone)
        <p>{{ trans('plugins/job-board::job-application.tables.phone') }}:
            <i><a href="tel:{{ $jobApplication->phone }}">{{ $jobApplication->phone }}</a></i>
        </p>
    @endif

    <p>{{ trans('plugins/job-board::job-application.tables.email') }}:
        <i><a href="mailto:{{ $jobApplication->email }}">{{ $jobApplication->email }}</a></i>
    </p>

    @if (!$jobApplication->is_external_apply)
        @if ($jobApplication->resume)
            <p>{{ trans('plugins/job-board::job-application.tables.resume') }}:
                <a href="{{ route(auth()->check() && is_in_admin(true) ? 'job-applications.download-cv' : 'public.account.applicants.download-cv', $jobApplication->id) }}" target="_blank">{{ trans('plugins/job-board::job-application.tables.download_resume') }} <i class="fa fa-external-link"></i></a>
            </p>
        @endif

        @if ($jobApplication->cover_letter)
            <p>{{ trans('plugins/job-board::job-application.tables.cover_letter') }}:
                <a href="{{ RvMedia::url($jobApplication->cover_letter) }}" target="_blank"><i>{{ RvMedia::url($jobApplication->cover_letter) }}</i> <i class="fa fa-external-link"></i></a>
            </p>
        @endif
    @endif

    @if ($jobApplication->message)
        <p>{{ trans('plugins/job-board::job-application.tables.message') }}:</p>
        <pre class="message-content">{{ $jobApplication->message }}</pre>
    @endif
@endif

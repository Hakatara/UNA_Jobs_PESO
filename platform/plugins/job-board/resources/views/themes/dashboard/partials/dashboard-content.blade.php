<div class="row">
    <div class="col-lg-4">
        <div class="card-style-1 hover-up">
            <div class="card-image">
                <img src="{{ asset('vendor/core/plugins/job-board/images/dashboard/computer.svg') }}" alt="{{ __('Total Jobs') }}">
            </div>

            <div class="card-info">
                <div class="card-title">
                    <h3>{{ $totalJobs }}
                        <span class="font-sm">{{ __('Total Jobs') }}</span>
                    </h3>
                </div>
                <p class="color-text-paragraph-2">{{ __('All status included') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-style-1 hover-up">
            <div class="card-image">
                <img src="{{ asset('vendor/core/plugins/job-board/images/dashboard/computer.svg') }}" alt="{{ __('Total Applicants') }}">
            </div>

            <div class="card-info">
                <div class="card-title">
                    <h3>{{ $totalApplicants }}
                        <span class="font-sm">{{ __('Total Applicants') }}</span>
                    </h3>
                </div>
                <p class="color-text-paragraph-2">{{ __('In :total Jobs', ['total' => $totalJobs]) }}</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="panel-white">
            <header class="panel-head">
                <h5>{{ __('New Applicants') }}</h5>
            </header>
            <article class="panel-body">
                <div class="new-member-list">
                    @forelse ($newApplicants as $item)
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6>{{ $item->full_name }}</h6>
                                    <p class="text-muted font-xs">{{ $item->email }}</p>
                                </div>
                            </div>
                            <a href="{{ route('public.account.applicants.edit', $item->id) }}" class="btn btn-xs px-2">
                                <span>{{ __('View') }}</span>
                            </a>
                        </div>
                    @empty
                        <p class="text-muted">{{ __('No new applicants') }}</p>
                    @endforelse
                </div>
            </article>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel-white">
            <header class="panel-head">
                <h5>{{ __('Recent activities') }}</h5>
            </header>
            <article class="panel-body">
                <ul class="vertical-timeline list-unstyled font-sm">
                    @forelse ($activities as $activity)
                        <li class="event-list">
                            <div class="media">
                                <div class="me-3">
                                    <h6 class="text-nowrap">
                                        <i class="fa-solid fa-clock"></i> <span>{{ $activity->created_at->diffForHumans() }} <i class="fa-solid fa-arrow-right-long icon-arrow"></i></span>
                                    </h6>
                                </div>
                                <div class="media-body">
                                    <div>{!! BaseHelper::clean($activity->getDescription(false)) !!}</div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li>
                            <p class="text-muted">{{ __('No activities') }}</p>
                        </li>
                    @endforelse
                </ul>
            </article>
        </div>
    </div>
</div>


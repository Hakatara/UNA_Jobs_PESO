@extends(JobBoardHelper::viewPath('dashboard.layouts.master'))
@section('content')
    <div class="row">
        <div class="col-lg-3">
            <div class="card-style-1 hover-up">
                <div class="card-image"> <img src="{{ asset('vendor/core/plugins/job-board/images/dashboard/computer.svg') }}" alt="{{ __('Total views') }}"></div>
                <div class="card-info">
                    <div class="card-title">
                        <h3>
                            <span>{{ number_format($job->views) }}</span>
                            <span class="font-sm">{{ __('All status included') }}</span>
                        </h3>
                    </div>
                    <p class="color-text-paragraph-2">{{ __('Total views') }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card-style-1 hover-up">
                <div class="card-image"> <img src="{{ asset('vendor/core/plugins/job-board/images/dashboard/look.svg') }}" alt="{{ __('Views today') }}"></div>
                <div class="card-info">
                    <div class="card-title">
                        <h3>
                            <span>{{ number_format($viewsToday) }}</span>
                            <span class="font-sm">{{ __('All status included') }}</span>
                        </h3>
                    </div>
                    <p class="color-text-paragraph-2">{{ __('Views today') }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card-style-1 hover-up">
                <div class="card-image"> <img src="{{ asset('vendor/core/plugins/job-board/images/dashboard/man.svg') }}" alt="{{ __('Number of favorites') }}"></div>
                <div class="card-info">
                    <div class="card-title">
                        <h3>
                            <span>{{ number_format($numberSaved) }}</span>
                            <span class="font-sm">{{ __('All status included') }}</span>
                        </h3>
                    </div>
                    <p class="color-text-paragraph-2">{{ __('Number of favorites') }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card-style-1 hover-up">
                <div class="card-image"> <img src="{{ asset('vendor/core/plugins/job-board/images/dashboard/man.svg') }}" alt="{{ __('Applicants') }}"></div>
                <div class="card-info">
                    <div class="card-title">
                        <h3>
                            <span>{{ number_format($applicants) }}</span>
                            <span class="font-sm">{{ __('All status included') }}</span>
                        </h3>
                    </div>
                    <p class="color-text-paragraph-2">{{ __('Applicants') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-white">
        <header class="panel-head">
            <h4 class="card-title">{{ __('Top Referrers') }}</h4>
        </header>
        <div class="panel-body">
            <div class="table-responsive">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="align-middle" scope="col">#</th>
                                <th class="align-middle" scope="col">{{ __('URL') }}</th>
                                <th class="align-middle" scope="col">{{ __('Views') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($referrers as $referrer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $referrer->referer }}</td>
                                    <td>{{ $referrer->total }}</td>
                                </tr>
                            @empty
                                <tr class="bg-white">
                                    <td colspan="3" class="text-center text-sm text-gray-500 py-4">{{ __('No data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-white">
        <header class="panel-head">
            <h4 class="card-title">{{ __('Top Countries') }}</h4>
        </header>
        <div class="panel-body">
            <div class="table-responsive">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="align-middle" scope="col">#</th>
                                <th class="align-middle" scope="col">{{ __('Country') }}</th>
                                <th class="align-middle" scope="col">{{ __('Views') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($countries as $country)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $country->country_full }}</td>
                                    <td>{{ $country->total }}</td>
                                </tr>
                            @empty
                                <tr class="bg-white">
                                    <td colspan="3" class="text-center text-sm text-gray-500 py-4">{{ __('No data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

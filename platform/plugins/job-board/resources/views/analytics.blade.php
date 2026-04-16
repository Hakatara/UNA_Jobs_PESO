@extends('core/base::layouts.master')
@section('content')
    <div class="row row-cols-xxl-6 row-cols-md-4 row-cols-sm-2">
        <div class="col">
            <div class="dashboard-stat dashboard-stat-v2" style="background-color: #32c5d2; color: #fff">
                <div class="visual">
                    <i class="far fa-eye" style="opacity: .1;"></i>
                </div>
                <div class="details">
                    <div class="number">
                        <span data-counter="counterup" data-value="{{ $job->views }}">0</span>
                    </div>
                    <div class="desc">{{ trans('plugins/job-board::job.analytics.total_views') }}</div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="dashboard-stat dashboard-stat-v2" style="background-color: #8e44ad; color: #fff">
                <div class="visual">
                    <i class="far fa-clock" style="opacity: .1;"></i>
                </div>
                <div class="details">
                    <div class="number">
                        <span data-counter="counterup" data-value="{{ $viewsToday }}">0</span>
                    </div>
                    <div class="desc">{{ trans('plugins/job-board::job.analytics.views_today') }}</div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="dashboard-stat dashboard-stat-v2" style="background-color: #e7505a; color: #fff">
                <div class="visual">
                    <i class="fas fa-heart" style="opacity: .1;"></i>
                </div>
                <div class="details">
                    <div class="number">
                        <span data-counter="counterup" data-value="{{ $numberSaved }}">0</span>
                    </div>
                    <div class="desc">{{ trans('plugins/job-board::job.analytics.number_of_favorites') }}</div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="dashboard-stat dashboard-stat-v2" style="background-color: #32c5d2; color: #fff">
                <div class="visual">
                    <i class="fas fa-users" style="opacity: .1;"></i>
                </div>
                <div class="details">
                    <div class="number">
                        <span data-counter="counterup" data-value="{{ $applicants }}">0</span>
                    </div>
                    <div class="desc">{{ trans('plugins/job-board::job.analytics.applicants') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="list_widgets">
        <div class="col-sm-6 col-12 widget_item">
            <div class="portlet light bordered portlet-no-padding">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fas fa-link font-dark fw-bold"></i>
                        <span class="caption-subject font-dark">{{ __('Top Referrers') }}</span>
                    </div>
                </div>
                <div class="portlet-body equal-height widget-content">
                    @if ($referrers->count() > 0)
                        <div class="scroller">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('URL') }}</th>
                                        <th>{{ __('Views') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referrers as $referrer)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $referrer->referer }}</td>
                                            <td>{{ $referrer->total }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('core/dashboard::partials.no-data', ['message' => __('No data')])
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 widget_item">
            <div class="portlet light bordered portlet-no-padding">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fas fa-globe font-dark fw-bold"></i>
                        <span class="caption-subject font-dark">{{ __('Top Countries') }}</span>
                    </div>
                </div>
                <div class="portlet-body equal-height widget-content">
                    @if ($countries->count() > 0)
                        <div class="scroller">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Country') }}</th>
                                        <th>{{ __('Views') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($countries as $country)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $country->country_full }}</td>
                                            <td>{{ $country->total }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('core/dashboard::partials.no-data', ['message' => __('No data')])
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@push('footer')
    <script>
        $(document).ready(function () {
            $('.equal-height').equalHeights();
        });
    </script>
@endpush

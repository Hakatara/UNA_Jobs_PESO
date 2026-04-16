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
                        <span data-counter="counterup" data-value="{{ $company->views }}">0</span>
                    </div>
                    <div class="desc"> {{ __('Views') }} </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="dashboard-stat dashboard-stat-v2" style="background-color: #8e44ad; color: #fff">
                <div class="visual">
                    <i class="fas fa-briefcase" style="opacity: .1;"></i>
                </div>
                <div class="details">
                    <div class="number">
                        <span data-counter="counterup" data-value="{{ $company->jobs_count }}">0</span>
                    </div>
                    <div class="desc"> {{ __('Jobs') }} </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-12 widget_item">
            <div class="portlet light bordered portlet-no-padding">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fas fa-briefcase font-dark fw-bold"></i>
                        <span class="caption-subject font-dark">{{ __('Jobs') }}</span>
                    </div>
                </div>
                <div class="portlet-body equal-height widget-content">
                    @if ($company->jobs->count() > 0)
                        <div class="scroller">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Applied') }}</th>
                                        <th>{{ __('Views') }}</th>
                                        <th>{{ __('Created At') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($company->jobs as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><a href="{{ $item->url }}" target="_blank">{{ $item->name }}</a></td>
                                            <td>{{ $item->number_of_applied }}</td>
                                            <td>{{ $item->views }}</td>
                                            <td>{{ BaseHelper::formatDate($item->created_at) }}</td>
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

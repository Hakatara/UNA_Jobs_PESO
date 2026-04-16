@extends(JobBoardHelper::viewPath('dashboard.layouts.master'))

@section('content')
    <div class="card-body">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4">
            @foreach ($packages as $package)
                <div class="col">
                    <div class="card panel-white hover-up overflow-hidden">
                        @if ($package->percent_save)
                            <span class="position-absolute end-0 badge rounded-pill bg-danger mt-2 me-2 z-index-9">
                                {{ $package->percent_save_text }}
                                <span class="visually-hidden">{{ trans('plugins/job-board::dashboard.save') }}</span>
                            </span>
                        @endif

                        @if ($package->is_default)
                            <span class="position-absolute bg-success rounded-0 text-white fs-sm px-1 py-2 z-index-9 label-feature">
                                <i class="fas fa-certificate fa-fw"></i>
                            </span>
                        @endif

                        <div class="panel-head background-header-packages d-flex justify-content-center align-items-center">
                                <h4 class="card-title">{{ $package->name }}</h4>
                        </div>
                        <div class="panel-body text-center">
                            <div class="card-text text-muted">
                                @if ($package->price)
                                    <p class="m-0">{{ $package->price_per_job_text }}</p>
                                @else
                                    <p class="m-0">{{ $package->number_jobs_free }}</p>
                                @endif
                                <p>{{ $package->price_text_with_sale_off }}</p>
                                {!! Form::open([
                                    'route' => 'public.account.package.subscribe.put',
                                    'method' => 'PUT',
                                ]) !!}
                                    {!! Form::hidden('id', $package->id) !!}
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-primary rounded font-sm mt-15"
                                        {{ $package->isPurchased() ? 'disabled' : '' }}
                                    >
                                        {{ $package->isPurchased()  ? __('Register') : __('Register') }}
                                    </button>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- col.// -->
            @endforeach
        </div>
        <!-- row.// -->
    </div>
    @if (auth('account')->user()->transactions()->exists())
        <h4 class="with-actions my-3">{{ trans('plugins/job-board::dashboard.transactions_title') }}</h4>
        <payment-history-component url="{{ route('public.account.ajax.transactions') }}"></payment-history-component>
    @endif
@stop

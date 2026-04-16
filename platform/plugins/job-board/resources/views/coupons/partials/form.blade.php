<div class="panel-white mt-3 mb-0">
    <div class="panel-head">
        <h5>{{ __('Your order') }}</h5>
    </div>
    <div class="panel-body">
        <div class="card-style-5">
            <div class="card-title">
                <h6 class="font-sm text-muted fw-bold">{{ $package->name }}</h6>
            </div>
            <div class="font-sm text-end w-100 text-muted">{{ format_price($package->price) }}</div>
        </div>
        @if(session()->has('applied_coupon_code'))
            <div class="card-style-5">
                <div class="card-title">
                    <h6 class="font-sm text-muted">{{ trans('plugins/job-board::coupon.coupon_code') }}</h6>
                </div>
                <div class="font-sm text-end w-100 text-muted">{{ session()->get('applied_coupon_code') }}</div>
            </div>
            @if(session()->get('coupon_discount_amount') > 0)
                <div class="card-style-5">
                    <div class="card-title">
                        <h6 class="font-sm text-muted">{{ trans('plugins/job-board::coupon.discount_amount') }}</h6>
                    </div>
                    <div class="font-sm text-end w-100 text-muted">{{ format_price(session()->get('coupon_discount_amount')) }}</div>
                </div>
            @endif
        @endif
        <div class="card-style-5">
            <div class="card-title">
                <h6 class="font-sm">{{ trans('plugins/job-board::coupon.total') }}</h6>
            </div>
            <div class="font-sm text-end w-100">{{ format_price($totalAmount) }}</div>
        </div>
    </div>
</div>

<button class="btn btn-link ps-0 text-decoration-none toggle-coupon-form" type="button">{{ trans('plugins/job-board::coupon.toggle_coupon_form_text') }}</button>

<div class="panel-white coupon-form mt-3" @style(['display: none' => ! session()->has('applied_coupon_code')])>
    <div class="panel-body">
        @if(session('applied_coupon_code'))
            <div class="alert alert-success mb-0 w-100">
                <span>{{ __('Coupon code: :code', ['code' => session('applied_coupon_code')]) }}</span>

                <button class="float-end mt-1 p-0 btn btn-link text-danger text-decoration-none remove-coupon-code" data-url="{{ route('public.account.coupon.remove') }}" type="button">
                    <i class="fa fa-trash"></i>
                    {{ __('Remove') }}
                </button>
            </div>
        @else
            <div class="form-group">
                <label for="coupon_code" class="form-label">{{ trans('plugins/job-board::coupon.coupon_code') }}</label>
                <div class="input-group">
                    <input type="text" id="coupon_code" name="coupon_code" class="form-control form-control-sm" placeholder="{{ trans('plugins/job-board::coupon.coupon_code_placeholder') }}" value="{{ BaseHelper::clean(old('coupon_code')) }}">
                    <button class="btn btn-sm btn-default apply-coupon-code" data-url="{{ route('public.account.coupon.apply') }}" type="button" style="line-height: unset">
                        {{ trans('plugins/job-board::coupon.apply_coupon_code') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

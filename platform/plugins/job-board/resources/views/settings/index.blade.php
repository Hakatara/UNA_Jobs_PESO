@extends('core/base::layouts.master')
@section('content')
    {!! Form::open(['url' => route('job-board.settings'), 'class' => 'main-setting-form']) !!}
    <div class="max-width-1200">

        <x-core-setting::section
            :title="trans('plugins/job-board::currency.currencies')"
            :description="trans('plugins/job-board::currency.setting_description')"
        >
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_enable_auto_detect_visitor_currency">{{ trans('plugins/job-board::currency.enable_auto_detect_visitor_currency') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_auto_detect_visitor_currency"
                           value="1"
                           @if (setting('job_board_enable_auto_detect_visitor_currency', 0) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_auto_detect_visitor_currency"
                           value="0"
                           @if (setting('job_board_enable_auto_detect_visitor_currency', 0) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>

                {!! Form::helper(trans('plugins/job-board::currency.auto_detect_visitor_currency_description')) !!}
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_add_space_between_price_and_currency">{{ trans('plugins/job-board::job-board.setting.add_space_between_price_and_currency') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_add_space_between_price_and_currency"
                           value="1"
                           @if (setting('job_board_add_space_between_price_and_currency', 0) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_add_space_between_price_and_currency"
                           value="0"
                           @if (setting('job_board_add_space_between_price_and_currency', 0) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3 row">
                <div class="col-sm-6">
                    <label class="text-title-field" for="job_board_thousands_separator">{{ trans('plugins/job-board::job-board.setting.thousands_separator') }}</label>
                    <div class="ui-select-wrapper">
                        <select class="ui-select" name="job_board_thousands_separator" id="job_board_thousands_separator">
                            <option value="," @if (setting('job_board_thousands_separator', ',') == ',') selected @endif>{{ trans('plugins/job-board::job-board.setting.separator_comma') }}</option>
                            <option value="." @if (setting('job_board_thousands_separator', ',') == '.') selected @endif>{{ trans('plugins/job-board::job-board.setting.separator_period') }}</option>
                            <option value="space" @if (setting('job_board_thousands_separator', ',') == 'space') selected @endif>{{ trans('plugins/job-board::job-board.setting.separator_space') }}</option>
                        </select>
                        <svg class="svg-next-icon svg-next-icon-size-16">
                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                        </svg>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="text-title-field" for="job_board_decimal_separator">{{ trans('plugins/job-board::job-board.setting.decimal_separator') }}</label>
                    <div class="ui-select-wrapper">
                        <select class="ui-select" name="job_board_decimal_separator" id="job_board_decimal_separator">
                            <option value="." @if (setting('job_board_decimal_separator', '.') == '.') selected @endif>{{ trans('plugins/job-board::job-board.setting.separator_period') }}</option>
                            <option value="," @if (setting('job_board_decimal_separator', '.') == ',') selected @endif>{{ trans('plugins/job-board::job-board.setting.separator_comma') }}</option>
                            <option value="space" @if (setting('job_board_decimal_separator', '.') == 'space') selected @endif>{{ trans('plugins/job-board::job-board.setting.separator_space') }}</option>
                        </select>
                        <svg class="svg-next-icon svg-next-icon-size-16">
                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                        </svg>
                    </div>
                </div>
            </div>

            <textarea name="currencies"
                      id="currencies"
                      class="hidden">{!! json_encode($currencies) !!}</textarea>
            <textarea name="deleted_currencies"
                      id="deleted_currencies"
                      class="hidden"></textarea>
            <div class="swatches-container">
                <div class="header clearfix">
                    <div class="swatch-item">
                        {{ trans('plugins/job-board::currency.name') }}
                    </div>
                    <div class="swatch-item">
                        {{ trans('plugins/job-board::currency.symbol') }}
                    </div>
                    <div class="swatch-item swatch-decimals">
                        {{ trans('plugins/job-board::currency.number_of_decimals') }}
                    </div>
                    <div class="swatch-item swatch-exchange-rate">
                        {{ trans('plugins/job-board::currency.exchange_rate') }}
                    </div>
                    <div class="swatch-item swatch-is-prefix-symbol">
                        {{ trans('plugins/job-board::currency.is_prefix_symbol') }}
                    </div>
                    <div class="swatch-is-default">
                        {{ trans('plugins/job-board::currency.is_default') }}
                    </div>
                    <div class="remove-item">{{ trans('plugins/job-board::currency.remove') }}</div>
                </div>
                <ul class="swatches-list">

                </ul>
                <div class="clearfix"></div>
                {!! Form::helper(trans('plugins/job-board::currency.instruction')) !!}
                <a href="#" class="js-add-new-attribute">
                    {{ trans('plugins/job-board::currency.new_currency') }}
                </a>
            </div>
        </x-core-setting::section>
        <x-core-setting::section
            :title="trans('plugins/job-board::job-board.company_settings')"
            :description="trans('plugins/job-board::job-board.company_settings_description')"
        >
            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_company_name_for_invoicing">{{ trans('plugins/job-board::job-board.setting.invoicing.company_name') }}</label>
                <input type="text" class="form-control" name="job_board_company_name_for_invoicing" value="{{ setting('job_board_company_name_for_invoicing', theme_option('site_title')) }}">
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_company_address_for_invoicing">{{ trans('plugins/job-board::job-board.setting.invoicing.company_address') }}</label>
                <input type="text" class="form-control" name="job_board_company_address_for_invoicing" value="{{ setting('job_board_company_address_for_invoicing') }}">
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_company_email_for_invoicing">{{ trans('plugins/job-board::job-board.setting.invoicing.company_email') }}</label>
                <input type="text" class="form-control" name="job_board_company_email_for_invoicing" value="{{ setting('job_board_company_email_for_invoicing', get_admin_email()->first()) }}">
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_company_phone_for_invoicing">{{ trans('plugins/job-board::job-board.setting.invoicing.company_phone') }}</label>
                <input type="text" class="form-control" name="job_board_company_phone_for_invoicing" value="{{ setting('job_board_company_phone_for_invoicing') }}">
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_company_logo_for_invoicing">{{ trans('plugins/job-board::job-board.setting.invoicing.company_logo') }}</label>
                {!! Form::mediaImage('job_board_company_logo_for_invoicing', setting('job_board_company_logo_for_invoicing') ?: theme_option('logo'), ['allow_thumb' => false]) !!}
            </div>

            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_using_custom_font_for_invoice">{{ trans('plugins/job-board::job-board.setting.using_custom_font_for_invoice') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_using_custom_font_for_invoice"
                           value="1"
                           @if (setting('job_board_using_custom_font_for_invoice', 0) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_using_custom_font_for_invoice"
                           value="0"
                           @if (setting('job_board_using_custom_font_for_invoice', 0) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>

            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_invoice_font_family">{{ trans('plugins/job-board::job-board.setting.invoice_font_family') }}</label>
                {!! Form::googleFonts('job_board_invoice_font_family', setting('job_board_invoice_font_family')) !!}
            </div>

            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_invoice_support_arabic_language">{{ trans('plugins/job-board::job-board.setting.invoice_support_arabic_language') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_invoice_support_arabic_language"
                           value="1"
                           @if (setting('job_board_invoice_support_arabic_language', 0) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_invoice_support_arabic_language"
                           value="0"
                           @if (setting('job_board_invoice_support_arabic_language', 0) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>

            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_enable_invoice_stamp">{{ trans('plugins/job-board::job-board.setting.enable_invoice_stamp') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_invoice_stamp"
                           value="1"
                           @if (setting('job_board_enable_invoice_stamp', 1) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_enable_invoice_stamp"
                           value="0"
                           @if (setting('job_board_enable_invoice_stamp', 1) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_invoice_code_prefix">{{ trans('plugins/job-board::job-board.setting.invoice_code_prefix') }}</label>
                <input type="text" class="form-control" name="job_board_invoice_code_prefix" value="{{ setting('job_board_invoice_code_prefix', 'INV-') }}">
            </div>
        </x-core-setting::section>
        <x-core-setting::section
            :title="trans('plugins/job-board::job-board.other_settings')"
            :description="trans('plugins/job-board::job-board.other_settings_description')"
        >
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_enable_guest_apply">{{ trans('plugins/job-board::job-board.setting.enable_guest_apply') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_guest_apply"
                           value="1"
                           @if (JobBoardHelper::isGuestApplyEnabled()) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_enable_guest_apply"
                           value="0"
                           @if (!JobBoardHelper::isGuestApplyEnabled()) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_enabled_register_as_employer">{{ trans('plugins/job-board::job-board.setting.enabled_register_as_employer') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enabled_register_as_employer"
                           value="1"
                           @if (setting('job_board_enabled_register_as_employer', 1) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_enabled_register_as_employer"
                           value="0"
                           @if (setting('job_board_enabled_register_as_employer', 1) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="verify_account_email">{{ trans('plugins/job-board::job-board.setting.verify_account_email') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="verify_account_email"
                           value="1"
                           @if (setting('verify_account_email') == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="verify_account_email"
                           value="0"
                           @if (setting('verify_account_email') == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="verify_account_created_company">{{ trans('plugins/job-board::job-board.setting.verify_account_created_company') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="verify_account_created_company"
                           value="1"
                           @if (setting('verify_account_created_company', 1) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="verify_account_created_company"
                           value="0"
                           @if (setting('verify_account_created_company', 1) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_enable_credits_system">{{ trans('plugins/job-board::job-board.setting.enable_credits_system') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_credits_system"
                           value="1"
                           @if (JobBoardHelper::isEnabledCreditsSystem()) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_credits_system"
                           value="0"
                           @if (!JobBoardHelper::isEnabledCreditsSystem()) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_enable_post_approval">{{ trans('plugins/job-board::job-board.setting.enable_post_approval') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_enable_post_approval"
                           value="1"
                           @if (setting('job_board_enable_post_approval', 1) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_enable_post_approval"
                           value="0"
                           @if (setting('job_board_enable_post_approval', 1) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_expired_after_days">{{ trans('plugins/job-board::job-board.setting.job_expired_after_days') }}
                </label>
                <input type="number" class="form-control" name="job_expired_after_days" value="{{ JobBoardHelper::jobExpiredDays() }}">
            </div>

            <div class="form-group mb-3">
                <label class="text-title-field"
                       for="job_board_job_location_display">{{ trans('plugins/job-board::job-board.setting.job_location_display') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_job_location_display"
                           value="state_and_country"
                           @if (setting('job_board_job_location_display', 'state_and_country') == 'state_and_country') checked @endif>{{ trans('plugins/job-board::job-board.setting.state_and_country') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_job_location_display"
                           value="city_and_state"
                           @if (setting('job_board_job_location_display', 'state_and_country') == 'city_and_state') checked @endif>{{ trans('plugins/job-board::job-board.setting.city_and_state') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_job_location_display"
                           value="city_state_and_country"
                           @if (setting('job_board_job_location_display', 'state_and_country') == 'city_state_and_country') checked @endif>{{ trans('plugins/job-board::job-board.setting.city_state_and_country') }}
                </label>
            </div>

            <x-core-setting::radio
                name="job_board_zip_code_enabled"
                :label="trans('plugins/job-board::job-board.setting.enable_zip_code')"
                :value="JobBoardHelper::isZipCodeEnabled() ? 1 : 0"
                :options="[
                    1 => trans('core/setting::setting.general.yes'),
                    0 => trans('core/setting::setting.general.no'),
                ]"
            />

            @if (is_plugin_active('captcha'))
                <div class="mb-3 px-2 py-3 bg-light border rounded-4">
                    <div>
                        <h5>{{ trans('plugins/captcha::captcha.settings.title') }}</h5>
                    </div>
                    @if (setting('enable_captcha'))
                        <div class="form-group mb-3">
                            <label class="text-title-field"
                                   for="job_board_enable_recaptcha_in_register_page">{{ trans('plugins/job-board::job-board.setting.enable_recaptcha_in_register_page') }}
                            </label>
                            <label class="me-2">
                                <input type="radio" name="job_board_enable_recaptcha_in_register_page"
                                       value="1"
                                       @if (setting('job_board_enable_recaptcha_in_register_page', 0) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                            </label>
                            <label class="me-2">
                                <input type="radio" name="job_board_enable_recaptcha_in_register_page"
                                       value="0"
                                       @if (setting('job_board_enable_recaptcha_in_register_page', 0) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                            </label>
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-title-field"
                                   for="job_board_enable_recaptcha_in_apply_job">{{ trans('plugins/job-board::job-board.setting.enable_recaptcha_in_apply_job') }}
                            </label>
                            <label class="me-2">
                                <input type="radio" name="job_board_enable_recaptcha_in_apply_job"
                                       value="1"
                                       @if (setting('job_board_enable_recaptcha_in_apply_job', 0) == 1) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                            </label>
                            <label class="me-2">
                                <input type="radio" name="job_board_enable_recaptcha_in_apply_job"
                                       value="0"
                                       @if (setting('job_board_enable_recaptcha_in_apply_job', 0) == 0) checked @endif>{{ trans('core/setting::setting.general.no') }}
                            </label>
                        </div>
                    @else
                        <span class="help-ts">{{ trans('plugins/job-board::job-board.setting.enable_recaptcha_in_pages_description') }}</span>
                    @endif
                </div>
            @endif

            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_is_enabled_review_feature">{{ trans('plugins/job-board::job-board.setting.enable_review_feature') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_is_enabled_review_feature" value="1" @checked(JobBoardHelper::isEnabledReview())>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_is_enabled_review_feature" value="0" @checked(! JobBoardHelper::isEnabledReview())>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>

            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_disabled_public_profile">{{ trans('plugins/job-board::job-board.setting.disabled_public_profile') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="job_board_disabled_public_profile" value="1" @checked(JobBoardHelper::isDisabledPublicProfile())>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="job_board_disabled_public_profile" value="0" @checked(! JobBoardHelper::isDisabledPublicProfile())>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>

            <x-core-setting::radio
                name="job_board_hide_company_email_enabled"
                :label="trans('plugins/job-board::job-board.setting.hide_company_email')"
                :value="JobBoardHelper::hideCompanyEmailEnabled() ? 1 : 0"
                :options="[
                    1 => trans('core/setting::setting.general.yes'),
                    0 => trans('core/setting::setting.general.no'),
                ]"
            />

            <div class="form-group mb-3">
                <label class="text-title-field" for="job_board_default_account_avatar">{{ trans('plugins/job-board::job-board.setting.default_account_avatar') }}</label>
                {!! Form::mediaImage('job_board_default_account_avatar', setting('job_board_default_account_avatar')) !!}
                {{ Form::helper(trans('plugins/job-board::job-board.setting.default_account_avatar_helper')) }}
            </div>
        </x-core-setting::section>

        <div class="flexbox-annotated-section" style="border: none">
            <div class="flexbox-annotated-section-annotation">
                &nbsp;
            </div>
            <div class="flexbox-annotated-section-content">
                <button class="btn btn-info" type="submit">{{ trans('plugins/job-board::currency.save_settings') }}</button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection

@push('footer')
    <script id="currency_template" type="text/x-custom-template">
        <li data-id="__id__" class="clearfix">
            <div class="swatch-item" data-type="title">
                <input type="text" class="form-control" value="__title__">
            </div>
            <div class="swatch-item" data-type="symbol">
                <input type="text" class="form-control" value="__symbol__">
            </div>
            <div class="swatch-item swatch-decimals" data-type="decimals">
                <input type="number" class="form-control" value="__decimals__">
            </div>
            <div class="swatch-item swatch-exchange-rate" data-type="exchange_rate">
                <input type="number" class="form-control" value="__exchangeRate__" step="0.00000001">
            </div>
            <div class="swatch-item swatch-is-prefix-symbol" data-type="is_prefix_symbol">
                <div class="ui-select-wrapper">
                    <select class="ui-select">
                        <option value="1"
                                __isPrefixSymbolChecked__>{{ trans('plugins/job-board::currency.before_number') }}</option>
                        <option value="0"
                                __notIsPrefixSymbolChecked__>{{ trans('plugins/job-board::currency.after_number') }}</option>
                    </select>
                    <svg class="svg-next-icon svg-next-icon-size-16">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                    </svg>
                </div>
            </div>
            <div class="swatch-is-default" data-type="is_default">
                <input type="radio" name="currencies_is_default" value="__position__" __isDefaultChecked__>
            </div>
            <div class="remove-item"><a href="#" class="font-red"><i class="fa fa-trash"></i></a></div>
        </li>
    </script>
@endpush

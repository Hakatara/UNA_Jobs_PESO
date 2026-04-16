@php
    $account = auth('account')->user();
@endphp
{!! Assets::renderHeader(['core']) !!}
@if (BaseHelper::isRtlEnabled())
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/job-board/libraries/bootstrap/bootstrap.rtl.min.css') }}">
@else
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/job-board/libraries/bootstrap/bootstrap.min.css') }}">
@endif

<link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/themes/default.css') }}?v={{ get_cms_version() }}">

@if (BaseHelper::isRtlEnabled())
    <link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/rtl.css') }}?v={{ get_cms_version() }}">
@endif

<link rel="stylesheet" href="{{ asset('vendor/core/plugins/job-board/css/style-dashboard.css') }}?v={{ get_cms_version() }}">

@if (BaseHelper::isRtlEnabled())
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/job-board/css/style-dashboard-rtl.css') }}?v={{ get_cms_version() }}">
@endif

<header class="header sticky-bar">
    <div class="container">
        <div class="main-header">
            <div class="header-left">
                <div class="header-logo">
                    <a class="d-flex" href="{{ route('public.account.dashboard') }}">
                        @if ($logo = theme_option('logo_employer_dashboard', theme_option('logo')))
                            <img src="{{ RvMedia::getImageUrl($logo) }}" class="logo" alt="{{ theme_option('site_title') }}">
                        @endif
                    </a>
                </div>
            </div>
            <div class="header-right">
                <div class="block-signin">
                    @if ($account->canPost())
                        <ul class="nav">
                            <li>
                                <a class="btn btn-default" href="{{ route('public.account.jobs.create') }}">
                                    <i class="fa fa-edit mr-5"></i>
                                    {{ trans('plugins/job-board::dashboard.post_a_job') }}</a>
                            </li>
                        </ul>
                    @endif
                    <ul class="nav">
                        @if (JobBoardHelper::isEnabledCreditsSystem())
                            <li class="nav-item">
                                <a href="{{ route('public.account.packages') }}" class="nav-link btn-icon">
                                    <span>{{ __('Buy credits') }}</span>
                                    <span class="mr-2 badge badge-danger">{{ $account->credits }}</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                    <ul class="nav">
                        @if (is_plugin_active('language'))
                            @include(JobBoardHelper::viewPath('dashboard.partials.language-switcher'))
                        @endif
                    </ul>

                    <div class="member-login">
                        <img alt="" src="{{ $account->avatar_thumb_url }}">
                        <div class="info-member">
                            <strong class="color-brand-1">{{ $account->name }}</strong>
                            <div class="dropdown">
                                <a class="font-xs color-text-paragraph-2 icon-down" id="dropdownProfile" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">{{ __('My Account') }}</a>
                                <ul class="dropdown-menu dropdown-menu-light dropdown-menu-end" aria-labelledby="dropdownProfile">
                                    <li><a class="dropdown-item" href="{{ route('public.account.settings') }}">{{ __('My Account') }}</a></li>
                                    <li>
                                        <form action="{{ route('public.account.logout') }}" method="POST">
                                            @csrf
                                            <button class="dropdown-item">{{ __('Logout') }}</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

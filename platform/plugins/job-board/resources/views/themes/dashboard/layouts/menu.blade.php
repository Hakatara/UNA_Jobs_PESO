@php
    $menus = collect([
        [
            'key'     => 'public.account.dashboard',
            'icon'    => 'images/dashboard/dashboard.svg',
            'name'    => __('Dashboard'),
            'order'   => 1,
            'enabled' => true,
        ],
        [
            'key'     => 'public.account.jobs.index',
            'icon'    => 'images/dashboard/jobs.svg',
            'name'    => __('Jobs'),
            'routes'  => [
                'public.account.jobs.create',
                'public.account.jobs.edit',
                'public.account.jobs.analytics',
            ],
            'order'   => 2,
            'enabled' => true,
        ],
        [
            'key'     => 'public.account.companies.index',
            'icon'    => 'images/dashboard/recruiters.svg',
            'name'    => __('Company'),
            'routes'  => [
                'public.account.companies.create',
                'public.account.companies.edit',
            ],
            'order'   => 3,
            'enabled' => true,
        ],
        [
            'key'     => 'public.account.applicants.index',
            'icon'    => 'images/dashboard/jobs.svg',
            'name'    => __('Applicants'),
            'routes'  => [
                'public.account.applicants.edit',
            ],
            'order'   => 3,
            'enabled' => true,
        ],
        [
            'key'     => 'public.account.logout',
            'icon'    => 'images/dashboard/logout.svg',
            'name'    => __('Logout'),
            'order'   => 5,
            'enabled' => true,
            'routes'  => ['public.account.logout']
        ],
    ]);

    $currentRouteName = Route::currentRouteName();
@endphp

<nav class="nav-main-menu">
    <ul class="main-menu">
        @foreach ($menus->where('enabled')->sortBy('order') as $item)
            @if ($item['key'] === 'public.account.logout')
                <li class="menu-item">
                    <form action="{{ route($item['key']) }}" method="POST" id="formLogout">
                        @csrf
                        <a class="dashboard2" onclick="document.getElementById('formLogout').submit()">
                            <img src="{{ asset('vendor/core/plugins/job-board/' . $item['icon']) }}" alt="{{ $item['key'] }}">
                            <span class="name">{{ $item['name'] }}</span>
                        </a>
                    </form>
                </li>
            @else
                <li class="menu-item">
                    <a class="dashboard2 @if ($currentRouteName == $item['key'] || in_array($currentRouteName, Arr::get($item, 'routes', []))) active @endif" href="{{ route($item['key']) }}">
                        <img src="{{ asset('vendor/core/plugins/job-board/' . $item['icon']) }}" alt="{{ $item['key'] }}">
                        <span class="name">{{ $item['name'] }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>

@include(JobBoardHelper::viewPath('dashboard.partials.profile-completed'))

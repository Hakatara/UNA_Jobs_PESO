@include(JobBoardHelper::viewPath('dashboard.layouts.header'))
@include(JobBoardHelper::viewPath('dashboard.layouts.menu-mobile'))

<main class="main">

    <div class="nav"><a class="btn btn-expanded"></a>
        <nav class="nav-main-menu">
            @include(JobBoardHelper::viewPath('dashboard.layouts.menu'))
        </nav>
    </div>
    <div class="box-content">
        <div class="content">
            @include(JobBoardHelper::viewPath('dashboard.layouts.breadcrumb'))

            <div id="app">
                @yield('content')
            </div>
        </div>

        @include(JobBoardHelper::viewPath('dashboard.layouts.footer'))
    </div>
</main>

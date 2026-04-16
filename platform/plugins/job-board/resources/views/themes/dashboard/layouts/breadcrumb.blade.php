<div class="box-heading">
    <div class="box-title">
        <h3 class="mb-35">{{ page_title()->getTitle(false) }}</h3>
    </div>
    <div class="box-breadcrumb">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="{{ route('public.account.dashboard') }}">
                        <i class="fa fa-house mr-5"></i>{{ __('Employer') }}
                    </a>
                </li>
                <li><span>{{ page_title()->getTitle(false) }}</span></li>
            </ul>
        </div>
    </div>
</div>

@php
    $currencies = get_all_currencies();
@endphp

<div class="dropdown d-inline-block currency-switch">
    <a type="button" class="btn-currency-footer dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
       aria-expanded="false">
        {{ get_application_currency()->title }}
    </a>
    <div class="dropdown-menu dropdown-menu-end">
        @foreach ($currencies as $currency)
            <a href="{{ route('public.change-currency', $currency->title) }}" class="dropdown-item notify-item language"><span>{{ $currency->title }}</span></a>
        @endforeach
    </div>
</div>

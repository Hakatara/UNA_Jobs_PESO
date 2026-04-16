@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <form action="{{ route('coupons.create') }}" method="post">
            @csrf

            <v-coupon-form></v-coupon-form>
        </form>
    </div>

    @include('plugins/job-board::coupons.partials.scripts', compact('jsValidator'))
@endsection

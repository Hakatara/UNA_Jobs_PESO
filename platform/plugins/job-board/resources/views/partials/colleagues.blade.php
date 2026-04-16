<p>{{ __('We will notify your colleagues when someone applied.') }}</p>
<div class="row">
    <div class="col-sm-6" id="app-job-board">
        <employer-colleagues-component :data="{{ json_encode($model->employer_colleagues ?: []) }}"></employer-colleagues-component>
    </div>
</div>

@include('plugins/job-board::partials.add-company')

@pushonce('footer')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans["Add new"] = "{{ __('Add new') }}";
    </script>
@endpushonce

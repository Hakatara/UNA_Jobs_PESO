@php
    Theme::asset()->container('footer')->usePath()->add('no-ui-slider', 'js/noUISlider.js');
    Theme::asset()->container('footer')->usePath()->add('slider-js', 'js/slider.js');
    Theme::asset()->container('footer')->usePath()->add('slider-js', 'js/slider.js');

    if (theme_option('show_map_on_jobs_page', 'yes') === 'yes') {
        Theme::asset()->usePath()->add('leaflet-css', 'plugins/leaflet/leaflet.css');
        Theme::asset()->container('footer')->usePath()->add('leaflet-js', 'plugins/leaflet/leaflet.js');
        Theme::asset()->container('footer')->usePath()->add('leaflet-markercluster-js', 'plugins/leaflet/leaflet.markercluster-src.js');
    }
@endphp
<section class="section-box mt-30">
    <div class="container">
        <div class="row flex-row-reverse justify-content-center row-filter">
            <div class="col-lg-9 col-md-12 col-sm-12 row col-12 float-right jobs-listing">
                @include(Theme::getThemeNamespace('views.job-board.partials.job-items'), ['jobs' => $jobs])
            </div>

            @include(Theme::getThemeNamespace('views.job-board.partials.filter'), [
                 'jobCategories' => $jobCategories,
                 'jobCount' => $jobs->count() ?? 0,
                 'maxSalaryRange' => BaseHelper::clean($shortcode->max_salary_range),
                 'jobCategories' => $jobCategories,
                 'jobTypes' => $jobTypes,
                 'jobExperiences' => $jobExperiences,
                 'jobSkills' => $jobSkills,
            ])
        </div>
    </div>
</section>
@if(theme_option('show_map_on_jobs_page', 'yes') === 'yes')
    <script id="traffic-popup-map-template" type="text/x-jquery-tmpl">
        @include(Theme::getThemeNamespace('views.job-board.partials.map'))
    </script>
@endif


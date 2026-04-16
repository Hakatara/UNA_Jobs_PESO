<div class="col-lg-3 col-md-12 filter-section col-sm-12 col-12 sidebar-filter-mobile">
    <div class="sidebar-shadow none-shadow mb-30">
        <div class="backdrop"></div>
        <div class="sidebar-filters sidebar-filter-mobile__inner">
            {!! Form::open(['url' => route('public.ajax.jobs'), 'method' => 'GET', 'id' => 'jobs-filter-form', 'class' => 'sidebar-filter-mobile__content']) !!}
                <input type="hidden" name="page" data-value="{{ $jobs->currentPage() ?: 1 }}">
                <input type="hidden" name="keyword" value="{{ BaseHelper::stringify(request()->query('keyword')) }}">
                <input type="hidden" name="per_page">
                <input type="hidden" name="layout">
                <input type="hidden" name="sort_by"/>
                @if (isset($jobTags))
                    @foreach($jobTags as $jobTag)
                        <input type="hidden" name="job_tags[]" value="{{ $jobTag }}">
                    @endforeach
                @endif
                <input type="hidden" name="page" value="1">
                <div class="filter-block head-border mb-30">
                    <h5>
                        {{ __('Advanced Filters') }}
                        <a class="link-reset" href="{{ request()->url() }}">{{ __('Reset') }}</a>
                    </h5>
                </div>
                <div class="filter-block mb-30">
                    <div class="form-group select-style select-style-icon">
                        @if (is_plugin_active('location'))
                            <select
                                class="form-control submit-form-filter form-icons select-active select-location"
                                form="jobs-filter-form" id="selectCity"
                                name="location"
                                data-location-type="{{ theme_option('job_location_filter_by', 'state') }}"
                            >
                            </select>
                            <i class="fi-rr-marker"></i>
                        @endif
                    </div>
                </div>

                @if(! Route::is('public.job-category'))
                    <div class="filter-block mb-20">
                        <h5 class="medium-heading mb-15">{{ __('Industry') }}</h5>
                        <div class="form-group ps-custom-scrollbar">
                            <ul class="list-checkbox">
                                @foreach($jobCategories as $jobCategory)
                                    <li>
                                        <label class="cb-container">
                                            <input
                                                type="checkbox"
                                                class="submit-form-filter"
                                                name="job_categories[]"
                                                form="jobs-filter-form"
                                                @checked(in_array($jobCategory->id, (array) request()->input('job_categories', [])))
                                                value="{{ $jobCategory->id }}"
                                            >
                                            <span class="text-small">{{ $jobCategory->name }}</span>
                                            <span class="checkmark"></span>
                                        </label>
                                        <span class="number-item">{{ $jobCategory->jobs_count ?: 0 }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                

                <div class="filter-block mb-30">
                    <h5 class="medium-heading mb-10">{{ __('Experience Level') }}</h5>
                    <div class="form-group ps-custom-scrollbar">
                        <ul class="list-checkbox">
                            @foreach($jobExperiences as $jobExperience)
                                <li>
                                    <label class="cb-container">
                                        <input
                                            type="checkbox"
                                            name="job_experiences[]"
                                            class="submit-form-filter"
                                            id="check-job-experience-{{ $jobExperience->id }}" value="{{ $jobExperience->id }}"
                                            form="jobs-filter-form"
                                            @checked(in_array($jobExperience->id, (array) request()->input('job_experiences', [])))
                                        >
                                        <span class="text-small">{{ $jobExperience->name }}</span>
                                        <span class="checkmark"></span>
                                    </label>
                                    <span class="number-item">{{ $jobExperience->jobs_count ?: 0 }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="filter-block mb-30">
                    <h5 class="medium-heading mb-10">{{ __('Job Posted') }}</h5>
                    <div class="form-group">
                        <ul class="list-checkbox">
                            @foreach(JobBoardHelper::postedDateRanges() as $key => $item)
                                <li>
                                    <label class="cb-container">
                                        <input
                                            type="checkbox"
                                            class="submit-form-filter"
                                            name="date_posted"
                                            value="{{ $key }}"
                                            id="date-posted-{{ $key }}"
                                            form="jobs-filter-form"
                                            @checked($key == request()->input('date_posted'))
                                        >
                                        <span class="text-small">{{ $item['name'] }}</span>
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="filter-block mb-20">
                    <h5 class="medium-heading mb-15">{{ __('Job type') }}</h5>
                    <div class="form-group ps-custom-scrollbar">
                        <ul class="list-checkbox">
                            @foreach($jobTypes as $jobType)
                                <li>
                                    <label class="cb-container">
                                        <input
                                            type="checkbox"
                                            class="submit-form-filter"
                                            value="{{ $jobType->id }}"
                                            name="job_types[]"
                                            id="check-job-type-{{ $jobType->id }}"
                                            form="jobs-filter-form"
                                            @checked(in_array($jobType->id, (array) request()->input('job_types', [])))
                                        >
                                        <span class="text-small">{{ $jobType->name }}</span>
                                        <span class="checkmark"></span>
                                    </label>
                                    <span class="number-item">{{ $jobType->jobs_count ?: 0 }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="filter-block mb-20">
                    <div class="form-group ps-custom-scrollbar">
                        <ul class="list-checkbox">
                            @foreach($jobSkills as $skill)
                                <li>
                                    <label class="cb-container">
                                        <input
                                            type="checkbox"
                                            class="submit-form-filter"
                                            name="job_skills[]"
                                            id="btn-check-outlined-{{ $skill->id }}"
                                            autocomplete="off"
                                            form="jobs-filter-form"
                                            value="{{ $skill->id }}"
                                            @checked(in_array($skill->id, (array) request()->input('job_skills', [])))
                                        >
                                        <span class="text-small">{{ $skill->name }}</span>
                                        <span class="checkmark"></span>
                                    </label>
                                    <span class="number-item">{{ $skill->jobs_count ?: 0 }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

@php
    Theme::asset()->container('footer')->add('location-js', asset('vendor/core/plugins/location/js/location.js'), ['jquery']);
@endphp

@extends(Theme::getThemeNamespace('views.job-board.account.partials.layout-settings'))

@section('content')
    <div>
        <h3 class="mt-0 mb-15 color-brand-1">{{ __('My Account') }}</h3>
        {!! Form::open(['route' => 'public.account.post.settings', 'method' => 'POST', 'files' => true]) !!}
        <div class="mt-35 mb-40 box-info-profie avatar-view">
            <div class="image-profile">
                <img src="{{ $account->avatar_url }}" id="profile-img" alt="{{ $account->name }}">
            </div>
            <a class="btn btn-apply">{{ __('Upload Avatar') }}</a>
        </div>
        <div class="row form-contact">
            <div class="col-lg-12 col-md-12">
                <div class="form-group">
                    <label class="font-sm color-text-mutted mb-10" for="first_name">{{ __('First Name') }}</label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name"
                           name="first_name" value="{{ old('first_name', $account->first_name) }}" required placeholder="{{ __('Enter First Name') }}"/>
                    @error('first_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="font-sm color-text-mutted mb-10" for="last_name" >{{ __('Last Name') }}</label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                           id="last_name" name="last_name" value="{{ old('last_name', $account->last_name) }}" required placeholder="{{ __('Enter Last Name') }}"/>
                    @error('last_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="font-sm color-text-mutted mb-10" for="phone">{{ __('Phone') }}</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                           name="phone" id="phone" value="{{ old('phone', $account->phone) }}" placeholder="{{ __('Enter Phone') }}"/>
                    @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="font-sm color-text-mutted mb-10" for="dob">{{ __('Date of Birth') }}</label>
                    <input type="date" class="form-control @error('dob') is-invalid @enderror"
                           name="dob" id="dob" value="{{ old('dob', $account->dob ? $account->dob->format('Y-m-d') : '') }}"
                           max="{{ now()->format('Y-m-d') }}" pattern="\d{4}-\d{2}-\d{2}"/>
                    @error('dob')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @if (! $account->type->getKey() && setting('job_board_enabled_register_as_employer'))
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="type" class="form-label required">{{ __('Type') }}</label>
                            {!! Form::select('type', ['' => __('-- select --')] + Botble\JobBoard\Enums\AccountTypeEnum::labels(), old('type', $account->type), [
                                'class' => 'form-select',
                                'required' => true,
                            ]) !!}
                        </div>
                    </div>
                @endif
                <div class="form-group">
                    <label class="font-sm color-text-mutted mb-10" for="gender">{{ __('Gender') }}</label>
                    {!! Form::select('gender', ['' => __('-- select --')] + Botble\JobBoard\Enums\AccountGenderEnum::labels(), old('gender', $account->gender), [
                            'class' => 'form-control'
                        ]) !!}
                </div>
                <div class="form-group">
                    <div class="row location-custom-fields">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="country" class="font-sm color-text-mutted mb-10">{{ __('Country') }}</label>
                                {!! Form::select('country_id',
                                    ['' => __('Select country...')] + \Botble\Location\Models\Country::query()->pluck('name', 'id')->all(),
                                        old('country_id', $account), [
                                        'class' => 'form-control select2',
                                        'id' => 'country_id',
                                        'data-type' => 'country',
                                    ])
                                !!}
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="state_id" class="font-sm color-text-mutted mb-10">{{ __('State') }}</label>
                                {!! Form::select('state_id',
                                    ['' => __('Select state...')] + ($account->country_id ? $account->country->states()->pluck('name', 'id')->all() : []),
                                    old('state_id', $account), [
                                        'class' => 'form-control select2',
                                        'id' => 'state_id',
                                        'data-type' => 'state',
                                        'data-url' => route('ajax.states-by-country'),
                                    ])
                                !!}
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="city_id" class="font-sm color-text-mutted mb-10">{{ __('City') }}</label>
                                {!! Form::select('city_id',
                                    ['' => __('Select city...')] + ($account->state_id ? $account->state->cities()->pluck('name', 'id')->all() : []),
                                    old('city_id', $account), [
                                        'class' => 'form-control select2',
                                        'id' => 'city_id',
                                        'data-type' => 'city',
                                        'data-url' => route('ajax.cities-by-state'),
                                    ])
                                !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-sm color-text-mutted mb-10" for="address">{{ __('Address') }}</label>
                    <input type="text" class="form-control @error('description') is-invalid @enderror"
                           name="address" id="address" value="{{ old('address', $account->address) }}" placeholder="{{ __('Enter Address') }}"/>
                    @error('address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                @if ($account->isJobSeeker())
                    @if (count($jobSkills) || count($selectedJobSkills))
                        <div class="form-group mb-3">
                            <label for="favorite_skills" class="font-sm color-text-mutted mb-10">{{ __('Favorite Job Skills') }}</label>
                            <input type="text" class="tags form-control list-tagify" id="favorite_skills" name="favorite_skills" value="{{ implode(',', $selectedJobSkills) }}" data-keep-invalid-tags="false" data-list="{{ $jobSkills }}" data-user-input="false" placeholder="{{ __('Select from the list.') }}"/>
                            @error('favorite_skills')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    @endif

                    @if (count($jobTags) || count($selectedJobTags))
                        <div class="form-group mb-3">
                            <label for="favorite_tags" class="font-sm color-text-mutted mb-10">{{ __('Favorite Job Tags') }}</label>
                            <input type="text" class="tags form-control list-tagify" id="favorite_tags" name="favorite_tags" value="{{ implode(',', $selectedJobTags) }}" data-keep-invalid-tags="false" data-list="{{ $jobTags }}" data-user-input="false" placeholder="{{ __('Select from the list.') }}"/>
                            @error('favorite_tags')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    @endif
                @endif

                <div class="mt-4">
                    

                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="description" class="font-sm color-text-mutted mb-10">{{ __('Introduce Yourself') }}</label>
                                <textarea  @class(['form-control', 'is-invalid' => $errors->has('description')]) name="description"
                                           id="description" placeholder="{{ __('Enter Description') }}"
                                           rows="2">{!! BaseHelper::clean(old('description', $account->description)) !!}</textarea>
                                @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        
                        <!--end col-->
                        @if ($account->isJobSeeker())
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="attachments-cv" class="font-sm color-text-mutted mb-10">{{ __('Attachments CV') }}</label>
                                    <input type="file" class="form-control @error('resume') is-invalid @enderror"
                                           id="attachments-cv" name="resume" accept=".pdf,.doc,.docx" />
                                    @if ($account->resume)
                                        <div class="mb-4 mt-2">
                                            <p class="job-apply-resume-info"><i class="mdi mdi-information"></i> {!! BaseHelper::clean(__('Your current resume :resume. Just upload a new resume if you want to change it.', ['resume' => Html::link(RvMedia::url($account->resume), $account->resume, ['target' => '_blank'])->toHtml()])) !!}</p>
                                        </div>
                                    @endif
                                    @error('resume')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="attachments-cover-letter" class="font-sm color-text-mutted mb-10">{{ __('Cover letter') }}</label>
                                    <input type="file" @class(['form-control', 'is-invalid' => $errors->has('cover_letter')])
                                    id="attachments-cover-letter" name="cover_letter" accept=".pdf,.doc,.docx" />
                                    @if ($account->cover_letter)
                                        <div class="mb-4 mt-2">
                                            <p class="job-apply-resume-info"><i class="mdi mdi-information"></i> {!! BaseHelper::clean(__('Your current cover_letter :cover_letter. Just upload a new resume if you want to change it.', ['cover_letter' => Html::link(RvMedia::url($account->cover_letter), $account->cover_letter, ['target' => '_blank'])->toHtml()])) !!}</p>
                                        </div>
                                    @endif
                                    @error('cover_letter')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <!--end col-->
                        @endif
                    </div>
                    {!! apply_filters('account_settings_page', null, $account) !!}
                </div>
                <div class="border-bottom pt-10 pb-10 mb-30"></div>
                <div class="box-button mt-15">
                    <button class="btn btn-apply-big font-md font-bold">{{ __('Save All Changes') }}</button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    {!! Form::close() !!}
@endsection

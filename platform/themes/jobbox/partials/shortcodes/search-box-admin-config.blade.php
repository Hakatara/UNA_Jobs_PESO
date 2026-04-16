<div class="form-group">
    <label class="control-label">{{ __('Subtitle') }}</label>
    <input type="text" name="subtitle" value="{{ Arr::get($attributes, 'subtitle') }}" class="form-control"
           placeholder="{{ __('Subtitle') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control"
           placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Highlight text in title') }}</label>
    <input type="text" name="highlight_text" value="{{ Arr::get($attributes, 'highlight_text') }}" class="form-control"
           placeholder="{{ __('Highlight text in title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Description') }}</label>
    <textarea name="description" class="form-control" rows="3"
              placeholder="{{ __('Description') }}">{{ Arr::get($attributes, 'description') }}</textarea>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit featured company') }}</label>
    <input type="number" name="limit_company" value="{{ Arr::get($attributes, 'limit_company') }}" class="form-control"
           placeholder="{{ __('Add limit company number') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit job in one company') }}</label>
    <input type="number" name="limit_job" value="{{ Arr::get($attributes, 'limit_job') }}" class="form-control"
           placeholder="{{ __('Limit job in one company') }}">
</div>

@for($i = 1; $i <= 4 ; $i++)
    <div class="form-group">
        <label class="control-label">{{ __('Counter title ') . $i }}</label>
        <input type="text" name="counter_title_{{ $i }}" value="{{ Arr::get($attributes, 'counter_title_' . $i) }}"
               class="form-control" placeholder="{{ __('Counter title ') . $i }}">
    </div>
    <div class="form-group">
        <label class="control-label">{{ __('Counter number ') . $i }}</label>
        <input type="text" name="counter_number_{{ $i }}" value="{{ Arr::get($attributes, 'counter_number_' . $i) }}"
               class="form-control" placeholder="{{ __('Counter number ') . $i }}">
    </div>
@endfor

<div class="form-group">
    <label class="control-label">{{ __('Image') }}</label>
    {!! Form::mediaImage('image', Arr::get($attributes, 'image')) !!}
</div>

<div style="border: 1px solid #cac6c6; padding: 15px 10px; margin-bottom: 20px;">
    @for($i = 1; $i <= 6; $i++)
        <div class="form-group">
            <label class="control-label">{{ __('Banner :i', ['i' => $i]) }}</label>
            {!! Form::mediaImage('banner_image_' . $i, Arr::get($attributes, 'banner_image_' . $i)) !!}
        </div>
    @endfor
</div>

<div class="form-group">
    <label class="control-label">{{ __('Icon top banner') }}</label>
    {!! Form::mediaImage('icon_top_banner', Arr::get($attributes, 'icon_top_banner')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Icon bottom banner') }}</label>
    {!! Form::mediaImage('icon_bottom_banner', Arr::get($attributes, 'icon_bottom_banner')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Background image') }}</label>
    {!! Form::mediaImage('background_image', Arr::get($attributes, 'background_image')) !!}
</div>

@php($random = Str::random(20))

<div class="form-group">
    <label class="control-label" for="style_{{ $random }}">{{ __('Style') }}</label>
    {!!
        Form::customSelect('style', [
            'style-1' => __('Style 1'),
            'style-2' => __('Style 2'),
            'style-3' => __('Style 3'),
            'style-4' => __('Style 4'),
        ], Arr::get($attributes, 'style'), ['id' => 'style_' . $random])
    !!}
</div>

<div class="form-group search-box-style-shortcode"
     @if (Arr::get($attributes, 'style') != 'style-3') style="display: none" @endif>
    <label class="control-label">{{ __('Images') }}</label>
    {!! Form::mediaImages('images', explode(',', Arr::get($attributes, 'images', '')), ['allow_thumb' => false]) !!}
</div>

<div class="form-group search-box-keyword-shortcode">
    <label class="control-label">{{ __('Trending Keywords') }}</label>
    <input type="text" name="trending_keywords" value="{{ Arr::get($attributes, 'trending_keywords') }}"
           class="form-control" placeholder="{{ __('Trending keywords (comma separated)') }}">
</div>

<div class="input-group color-picker" data-color="{{ Arr::get($attributes, 'background_color') }}">
    {!! Form::text('background_color', Arr::get($attributes, 'background_color') ?? '#000', array_merge(['class' => 'form-control'], [])) !!}
    <span class="input-group-text">
    <span class="input-group-text colorpicker-input-addon"><i></i></span>
  </span>
</div>


<script>
    'use strict';

    $('#style_{{ $random }}').on('change', function () {
        if ('style-3' === $(this).val()) {
            $('.search-box-style-shortcode').show();
        } else {
            $('.search-box-style-shortcode').hide();
        }

        if ('style-2' === $(this).val()) {
            $('.search-box-keyword-shortcode').show();
        } else {
            $('.search-box-keyword-shortcode').hide();
        }
    });
</script>

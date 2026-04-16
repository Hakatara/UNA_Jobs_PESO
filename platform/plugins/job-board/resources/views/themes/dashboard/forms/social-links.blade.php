<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label for="facebook" class="control-label">{{ __('Facebook Profile URL') }}</label>
            <input type="url" class="form-control" name="facebook" id="facebook" value="{{ $company->facebook }}" placeholder="https://facebook.com/company-name">
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label for="twitter" class="control-label">{{ __('Twitter Profile URL') }}</label>
            <input type="url" class="form-control" name="twitter" id="twitter" value="{{ $company->twitter }}" placeholder="https://twitter.com/company-name">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label for="linkedin" class="control-label">{{ __('Linkedin Profile URL') }}</label>
            <input type="url" class="form-control" name="linkedin" id="linkedin" value="{{ $company->linkedin }}" placeholder="https://linkedin.com/company/company-name">
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label for="instagram" class="control-label">{{ __('Instagram Profile URL') }}</label>
            <input type="url" class="form-control" name="instagram" id="instagram" value="{{ $company->instagram }}" placeholder="https://instagram.com/company-name">
        </div>
    </div>
</div>

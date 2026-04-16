@push('footer')
    <div id="add-company-modal" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xs">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title"><i class="til_img"></i><strong>{{ __('Add new company') }}</strong></h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body with-padding">
                    <div class="form-group">
                        <label class="control-label required" for="company_name">{{ trans('core/base::forms.name') }}</label>
                        <input type="text" class="form-control" id="company_name" placeholder="{{ trans('core/base::forms.name') }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="float-start btn btn-warning" data-bs-dismiss="modal">{{ trans('core/base::tables.cancel') }}</button>
                    <a class="float-end btn btn-info" id="btn-add-company" href="#">{{ __('Save') }}</a>
                </div>
            </div>
        </div>
    </div>
@endpush

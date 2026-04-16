{!! Form::open(['url' => route('accounts.credits.add', $account->id)]) !!}
    <div class="next-form-section">
        <div class="next-form-grid">
            <div class="next-form-grid-cell">
                <label class="text-title-field">{{ trans('plugins/job-board::account.form.number_of_credits') }}</label>
                <input type="number" class="next-input" name="credits" placeholder="{{ trans('plugins/job-board::account.form.number_of_credits') }}" value="0">
            </div>
        </div>
        <div class="next-form-grid">
            <div class="next-form-grid-cell">
                <label class="text-title-field">{{ __('Description') }}</label>
                <textarea class="next-input" name="description" placeholder="{{ trans('plugins/job-board::account.form.description') }}" rows="5"></textarea>
            </div>
        </div>
    </div>
{!! Form::close() !!}

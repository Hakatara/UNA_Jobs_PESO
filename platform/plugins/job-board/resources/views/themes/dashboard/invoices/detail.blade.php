@extends(JobBoardHelper::viewPath('dashboard.layouts.master'))
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="invoice-info">
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <img src="{{ RvMedia::getImageUrl($invoice->company_logo) }}" style="max-height: 150px;" alt="{{ $invoice->company_name }}">
                        </div>
                        <div class="col-md-6 text-end">
                            <h2 class="mb-0 uppercase">{{ trans('plugins/job-board::invoice.heading') }}</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6 text-end">
                            <ul class="mb-0">
                                <li>{{ $invoice->customer_name }}</li>
                                <li>{{ $invoice->customer_email }}</li>
                                <li>{{ $invoice->customer_phone }}</li>
                                <li>{{ $invoice->customer_address }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-lg-4">
                        <strong class="text-brand">{{ trans('plugins/job-board::invoice.detail.code') }}:</strong>
                        #{{ $invoice->code }}
                    </div>
                    <div class="col-lg-4">
                        <strong class="text-brand">{{ trans('plugins/job-board::invoice.detail.issue_at') }}:</strong>
                        {{ $invoice->created_at->translatedFormat('j F, Y') }}
                    </div>
                    <div class="col-lg-4">
                        <strong class="text-brand">{{ trans('plugins/job-board::invoice.payment_method') }}:</strong>
                        {{ $invoice->payment->payment_channel->label() }}
                    </div>
                    <div class="col-12">
                        <hr>
                    </div>
                </div>
                <table class="table table-striped mb-3">
                    <thead>
                    <tr>
                        <th>{{ trans('plugins/job-board::invoice.detail.description') }}</th>
                        <th>{{ trans('plugins/job-board::invoice.detail.qty') }}</th>
                        <th class="text-center">{{ trans('plugins/job-board::invoice.total_amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td style="width: 70%">
                                <p class="mb-0">{{ $item->name }}</p>
                                @if ($item->description)
                                    <small>{{ $item->description }}</small>
                                @endif
                            </td>
                            <td style="width: 5%">{{ $item->qty }}</td>
                            <td style="width: 25%" class="text-center">{{ format_price($item->amount) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">{{ trans('plugins/job-board::invoice.detail.sub_total') }}:
                        </th>
                        <th class="text-center">{{ format_price($invoice->sub_total) }}</th>
                    </tr>

                    @if ((float)$invoice->tax_amount > 0)
                        <tr>
                            <th colspan="2" class="text-end">{{ trans('plugins/job-board::invoice.detail.tax') }}:</th>
                            <th class="text-center">{{ format_price($invoice->tax_amount) }}</th>
                        </tr>
                    @endif

                    @if ((float)$invoice->shipping_amount > 0)
                        <tr>
                            <th colspan="2"
                                class="text-end">{{ trans('plugins/job-board::invoice.detail.shipping_fee') }}:
                            </th>
                            <th class="text-center">{{ format_price($invoice->shipping_amount) }}</th>
                        </tr>
                    @endif
                    @if ((float)$invoice->discount_amount > 0)
                        <tr>
                            <th colspan="2" class="text-end">{{ trans('plugins/job-board::invoice.detail.discount') }}
                                :
                            </th>
                            <th class="text-center">{{ format_price($invoice->discount_amount) }}</th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="2" class="text-end">{{ trans('plugins/job-board::invoice.detail.grand_total') }}:
                        </th>
                        <th class="text-center">{{ format_price($invoice->amount) }}</th>
                    </tr>
                    </tfoot>
                </table>
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{ trans('plugins/job-board::invoice.detail.invoice_for') }}</h5>
                        <p class="font-sm">
                            <strong>{{ trans('plugins/job-board::invoice.detail.issue_at') }}
                                :</strong> {{ $invoice->created_at->format('j F, Y') }}<br>
                            <strong>{{ trans('plugins/job-board::invoice.detail.invoice_to') }}
                                :</strong> {{ $invoice->company_name }}<br>
                            @if ($invoice->customer_tax_id)
                                <strong>{{ trans('plugins/job-board::invoice.detail.tax_id') }}
                                    :</strong> {{ $invoice->customer_tax_id }}<br>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h5>{{ trans('plugins/job-board::invoice.total_amount') }}</h5>
                        <h3 class="mt-0 mb-0 text-danger">{{ format_price($invoice->amount) }}</h3>
                    </div>
                </div>
                <hr>
            </div>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('public.account.invoices.generate_invoice', ['id' => $invoice->id, 'type' => 'print']) }}" target="_blank"
               class="btn btn-danger">
                <i class="fas fa-print"></i> {{ trans('plugins/job-board::invoice.print') }}
            </a>
            <a href="{{ route('public.account.invoices.generate_invoice', ['id' => $invoice->id, 'type' => 'download']) }}"
               target="_blank" class="btn btn-success">
                <i class="fas fa-download"></i> {{ trans('plugins/job-board::invoice.download') }}
            </a>
        </div>
    </div>
@endsection

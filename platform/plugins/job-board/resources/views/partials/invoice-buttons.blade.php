<a href="{{ route('invoice.generate-invoice', ['id' => $invoice->id, 'type' => 'print']) }}" target="_blank" class="btn btn-success my-2">
    {{ trans('plugins/job-board::invoice.print') }}
</a>

<a href="{{ route('invoice.generate-invoice', ['id' => $invoice->id, 'type' => 'download']) }}" target="_blank" class="btn btn-danger my-2">
    {{ trans('plugins/job-board::invoice.download') }}
</a>

<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use App\Http\Controllers\Controller;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Invoice;
use Botble\JobBoard\Repositories\Interfaces\InvoiceInterface;
use Botble\JobBoard\Supports\InvoiceHelper;
use Botble\JobBoard\Tables\Fronts\InvoiceTable;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(Repository $config, protected InvoiceInterface $invoiceRepository)
    {
        Assets::setConfig($config->get('plugins.job-board.assets'));
    }

    public function index(InvoiceTable $invoiceTable)
    {
        PageTitle::setTitle(__('Invoices'));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.dashboard'))
            ->add(__('Manage Invoices'));

        SeoHelper::setTitle(__('Invoices'));

        return $invoiceTable->render(JobBoardHelper::viewPath('dashboard.table.base'));
    }

    public function show(int $id)
    {
        $invoice = $this->invoiceRepository->findOrFail($id);

        abort_unless($this->canViewInvoice($invoice), 404);

        $title = __('Invoice detail :code', ['code' => $invoice->code]);

        PageTitle::setTitle($title);

        SeoHelper::setTitle($title);

        return JobBoardHelper::view('dashboard.invoices.detail', compact('invoice'));
    }

    public function getGenerateInvoice(int $invoiceId, Request $request, InvoiceHelper $invoiceHelper)
    {
        $invoice = $this->invoiceRepository->findOrFail($invoiceId);

        abort_unless($this->canViewInvoice($invoice), 404);

        if ($request->input('type') === 'print') {
            return $invoiceHelper->streamInvoice($invoice);
        }

        return $invoiceHelper->downloadInvoice($invoice);
    }

    protected function canViewInvoice(Invoice $invoice): bool
    {
        return auth('account')->id() == $invoice->payment->customer_id;
    }
}

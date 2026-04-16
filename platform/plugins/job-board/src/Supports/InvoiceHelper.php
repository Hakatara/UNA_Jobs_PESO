<?php

namespace Botble\JobBoard\Supports;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PDFHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\TwigCompiler;
use Botble\JobBoard\Enums\InvoiceStatusEnum;
use Botble\JobBoard\Models\Invoice;
use Botble\JobBoard\Models\InvoiceItem;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Models\Package;
use Botble\Media\Facades\RvMedia;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Dompdf\Dompdf;
use Dompdf\Image\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Throwable;

class InvoiceHelper
{
    public static function store(array $data = []): bool
    {
        $orderIds = (array)$data['order_id'];

        $customerType = Arr::get($data, 'customer_type');

        if (! $customerType || ! class_exists($customerType)) {
            return false;
        }

        $customerId = Arr::get($data, 'customer_id');

        if (! $customerId) {
            return false;
        }

        $account = app($customerType)->find($customerId);

        if (! $account) {
            return false;
        }

        $payment = app(PaymentInterface::class)->getFirstBy([
            'charge_id' => $data['charge_id'],
            ['order_id', 'IN', $orderIds],
        ]);

        if (! $payment) {
            return false;
        }

        $isPaymentCompleted = $data['status'] === PaymentStatusEnum::COMPLETED;
        $amount = $data['amount'];

        $reference = Job::query()->whereIn('id', $orderIds)->with('metadata')->first();

        if ($reference && $reference->getMetaData('payment_charge_id', true)) {
            $company = $reference->company;
        } else {
            $reference = Package::query()->whereIn('id', $orderIds)->first();
            $company = $account->companies->first();
        }

        if (! $reference) {
            return false;
        }

        $invoice = new Invoice([
            'reference_id' => $reference->id,
            'reference_type' => get_class($reference),
            'customer_name' => $account->name,
            'company_name' => $company ? $company->name : null,
            'company_logo' => $company ? $company->logo : null,
            'customer_email' => $account->email,
            'customer_phone' => $account->phone,
            'customer_address' => $account->address,
            'customer_tax_id' => null,
            'sub_total' => $amount,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'coupon_code' => $data['coupon_code'] ?? null,
            'amount' => $amount,
            'payment_id' => $payment->id,
            'status' => $isPaymentCompleted ? InvoiceStatusEnum::COMPLETED : InvoiceStatusEnum::PENDING,
            'paid_at' => $isPaymentCompleted ? Carbon::now() : null,
        ]);

        $invoice->save();

        $invoice->items()->create([
            'reference_id' => $reference->id,
            'reference_type' => get_class($reference),
            'name' => $reference->name,
            'description' => null,
            'image' => null,
            'qty' => 1,
            'sub_total' => $amount,
            'tax_amount' => 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'amount' => $amount,
            'metadata' => [],
        ]);

        do_action(INVOICE_PAYMENT_CREATED, $invoice);

        return true;
    }

    public function downloadInvoice(Invoice $invoice): Response
    {
        return $this->makeInvoice($invoice)->download('invoice-' . $invoice->code . '.pdf');
    }

    public function streamInvoice(Invoice $invoice): Response
    {
        return $this->makeInvoice($invoice)->stream();
    }

    public function makeInvoice(Invoice $invoice): PDFHelper|Dompdf
    {
        $fontsPath = storage_path('fonts');

        if (! File::isDirectory($fontsPath)) {
            File::makeDirectory($fontsPath);
        }

        $content = null;
        $templateHtml = $this->getInvoiceTemplate();

        if ($templateHtml) {
            $twigCompiler = (new TwigCompiler())->addExtension(new TwigExtension());
            $content = $twigCompiler->compile($templateHtml, $this->getDataForInvoiceTemplate($invoice));

            if (setting('real_estate_invoice_support_arabic_language', 0) == 1) {
                $arabic = new Arabic();
                $p = $arabic->arIdentify($content);

                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    try {
                        $utf8ar = $arabic->utf8Glyphs(substr($content, $p[$i - 1], $p[$i] - $p[$i - 1]));
                        $content = substr_replace($content, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                    } catch (Throwable) {
                        continue;
                    }
                }
            }
        }

        Cache::$error_message = null;

        return Pdf::setWarnings(false)
            ->setOption('chroot', [public_path(), base_path()])
            ->setOption('tempDir', storage_path('app'))
            ->setOption('logOutputFile', storage_path('logs/pdf.log'))
            ->setOption('isRemoteEnabled', true)
            ->loadHTML($content, 'UTF-8')
            ->setPaper('a4');
    }

    public function getInvoiceTemplate(): ?string
    {
        $defaultPath = platform_path('plugins/job-board/resources/templates/invoice.tpl');
        $storagePath = storage_path('app/templates/invoice.tpl');

        if ($storagePath && File::exists($storagePath)) {
            $templateHtml = BaseHelper::getFileData($storagePath, false);
        } else {
            $templateHtml = File::exists($defaultPath) ? BaseHelper::getFileData($defaultPath, false) : '';
        }

        return $templateHtml;
    }

    public function getVariables(): array
    {
        return [
            'invoice.*' => __('Invoice information from database, ex: invoice.code, invoice.amount, ...'),
            'logo_full_path' => __('The site logo with full url'),
            'company_logo_full_path' => __('The company logo of invoice with full url'),
            'payment_method' => __('Payment method'),
            'payment_status' => __('Payment status'),
            'payment_description' => __('Payment description'),
            'settings.using_custom_font_for_invoice' => __('Check site is using custom font for invoice or not'),
            'settings.font_family' => __('The font family of invoice template'),
            'settings.enable_invoice_stamp' => __('Check have enabled the invoice stamp'),
            'settings.company_name_for_invoicing' => __('The company name of invoice'),
            'settings.company_address_for_invoicing' => __('The company address of invoice'),
            'settings.company_email_for_invoicing' => __('The company email of invoice'),
            'settings.company_phone_for_invoicing' => __('The company phone number of invoice'),
        ];
    }

    protected function getDataForInvoiceTemplate(Invoice $invoice): array
    {
        $logo = setting('job_board_company_logo_for_invoicing') ?: theme_option('logo');

        $companyLogo = $invoice->company_logo;

        if ($companyLogo && ! str_contains($invoice->company_logo, public_path())) {
            $companyLogo = RvMedia::getRealPath($companyLogo);
        }

        return [
            'invoice' => $invoice,
            'logo' => $logo,
            'logo_full_path' => $logo ? RvMedia::getRealPath($logo) : null,
            'site_title' => theme_option('site_title'),
            'company_logo_full_path' => $companyLogo,
            'payment_method' => $invoice->payment->payment_channel->label(),
            'payment_status' => $invoice->payment->status->label(),
            'payment_description' => ($invoice->payment->payment_channel == PaymentMethodEnum::BANK_TRANSFER && $invoice->payment->status == PaymentStatusEnum::PENDING)
                ? BaseHelper::clean(get_payment_setting('description', $invoice->payment->payment_channel))
                : null,
            'settings' => [
                'using_custom_font_for_invoice' => setting('job_board_using_custom_font_for_invoice'),
                'font_family' => setting('job_board_using_custom_font_for_invoice', 0) == 1
                    ? setting('job_board_invoice_font_family', '')
                    : 'DejaVu Sans',
                'enable_invoice_stamp' => setting('job_board_enable_invoice_stamp'),
                'company_name_for_invoicing' => setting('job_board_company_name_for_invoicing') ?: theme_option('site_title'),
                'company_address_for_invoicing' => setting('job_board_company_address_for_invoicing'),
                'company_email_for_invoicing' => setting('job_board_company_email_for_invoicing'),
                'company_phone_for_invoicing' => setting('job_board_company_phone_for_invoicing'),
            ],
        ];
    }

    public function getDataForPreview(): Invoice
    {
        $invoice = new Invoice([
            'code' => 'INV-1',
            'customer_name' => 'Odie Miller',
            'company_name' => 'LinkedIn',
            'company_logo' => public_path('vendor/core/plugins/job-board/images/sample-logo.png'),
            'customer_email' => 'contact@example.com',
            'customer_phone' => '+0123456789',
            'customer_address' => ' 14059 Triston Crossroad South Lillie, NH 84777-1634',
            'status' => InvoiceStatusEnum::PENDING,
        ]);

        $items = [];

        foreach (range(1, 2) as $i) {
            $amount = rand(10, 1000);
            $qty = rand(1, 10);

            $items[] = new InvoiceItem([
                'name' => "Item $i",
                'description' => "Description of item $i",
                'amount' => $amount,
                'qty' => $qty,
            ]);

            $invoice->amount += $amount * $qty;
            $invoice->sub_total = $invoice->amount;
        }

        $payment = new Payment([
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::PENDING,
        ]);

        $invoice->setRelation('payment', $payment);
        $invoice->setRelation('items', $items);

        return $invoice;
    }
}

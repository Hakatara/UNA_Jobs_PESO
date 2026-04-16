<?php

namespace Botble\JobBoard\Providers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\Form;
use Botble\Base\Facades\Html;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\TwigCompiler;
use Botble\JobBoard\Enums\InvoiceStatusEnum;
use Botble\JobBoard\Enums\JobApplicationStatusEnum;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\Invoice;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Models\Package;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\JobApplicationInterface;
use Botble\JobBoard\Repositories\Interfaces\PackageInterface;
use Botble\JobBoard\Services\CouponService;
use Botble\JobBoard\Supports\InvoiceHelper;
use Botble\JobBoard\Supports\TwigExtension;
use Botble\Media\Facades\RvMedia;
use Botble\Page\Models\Page;
use Botble\Page\Repositories\Interfaces\PageInterface;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(BASE_FILTER_APPEND_MENU_NAME, [$this, 'countPendingApplications'], 26, 2);
        add_filter(BASE_FILTER_MENU_ITEMS_COUNT, [$this, 'getMenuItemCount'], 26);
        if (function_exists('theme_option')) {
            add_action(RENDERING_THEME_OPTIONS_PAGE, [$this, 'addThemeOptions'], 55);
        }

        add_filter('cms_twig_compiler', function (TwigCompiler $twigCompiler) {
            if (! array_key_exists(TwigExtension::class, $twigCompiler->getExtensions())) {
                $twigCompiler->addExtension(new TwigExtension());
            }

            return $twigCompiler;
        }, 130);

        if (defined('PAYMENT_FILTER_PAYMENT_PARAMETERS')) {
            add_filter(PAYMENT_FILTER_PAYMENT_PARAMETERS, function ($html) {
                if (! auth('account')->check()) {
                    return $html;
                }

                return $html . Form::hidden('customer_id', auth('account')->id())->toHtml() .
                    Form::hidden('customer_type', Account::class)->toHtml();
            }, 123);
        }

        if (defined('PAYMENT_ACTION_PAYMENT_PROCESSED')) {
            add_action(PAYMENT_ACTION_PAYMENT_PROCESSED, function ($data) {
                $payment = PaymentHelper::storeLocalPayment($data);

                InvoiceHelper::store(array_merge($data, [
                    'discount_amount' => Session::get('coupon_discount_amount'),
                    'coupon_code' => Session::get('applied_coupon_code'),
                ]));

                if ($payment instanceof BaseModel) {
                    MetaBox::saveMetaBoxData($payment, 'subscribed_packaged_id', session('subscribed_packaged_id'));
                }
            }, 123);

            add_action(BASE_ACTION_META_BOXES, function ($context, $payment) {
                if (get_class($payment) == Payment::class && $context == 'advanced' && Route::currentRouteName() == 'payments.show') {
                    MetaBox::addMetaBox('additional_payment_data', __('Package information'), function () use ($payment) {
                        $subscribedPackageId = MetaBox::getMetaData($payment, 'subscribed_packaged_id', true);

                        if (! $subscribedPackageId) {
                            return null;
                        }

                        $package = app(PackageInterface::class)->findById($subscribedPackageId);

                        if (! $package) {
                            return null;
                        }

                        return view('plugins/job-board::partials.payment-extras', compact('package'));
                    }, get_class($payment), $context);
                }
            }, 128, 2);

            if (! $this->app->runningInConsole()) {
                add_action(INVOICE_PAYMENT_CREATED, function (Invoice $invoice) {
                    $customer = $invoice->payment->customer;
                    $localDisk = Storage::disk('local');
                    $invoiceName = 'invoice-' . $invoice->code . '.pdf';
                    $localDisk->put($invoiceName, (new InvoiceHelper())->makeInvoice($invoice)->output());

                    EmailHandler::setModule(JOB_BOARD_MODULE_SCREEN_NAME)
                        ->setVariableValues([
                            'account_name' => $customer->name,
                            'invoice_code' => $invoice->code,
                            'invoice_link' => route('public.account.invoices.show', $invoice->getKey()),
                        ])
                        ->sendUsingTemplate('invoice-payment-created', $customer->email, [
                            'attachments' => [$localDisk->path($invoiceName)],
                        ]);

                    $localDisk->delete($invoiceName);

                    $this->app->make(CouponService::class)->forgotCouponSession();
                });
            }
        }

        if (defined('PAYMENT_FILTER_REDIRECT_URL')) {
            add_filter(PAYMENT_FILTER_REDIRECT_URL, function ($checkoutToken) {
                $checkoutToken = $checkoutToken ?: session('subscribed_packaged_id');

                if (! $checkoutToken) {
                    return route('public.index');
                }

                if (str_contains($checkoutToken, url(''))) {
                    return $checkoutToken;
                }

                return route('public.account.package.subscribe.callback', $checkoutToken);
            }, 123);
        }

        if (defined('PAYMENT_FILTER_CANCEL_URL')) {
            add_filter(PAYMENT_FILTER_CANCEL_URL, function ($checkoutToken) {
                $checkoutToken = $checkoutToken ?: session('subscribed_packaged_id');

                if (! $checkoutToken) {
                    return route('public.index');
                }

                if (str_contains($checkoutToken, url(''))) {
                    return $checkoutToken;
                }

                return route('public.account.package.subscribe', $checkoutToken) . '?' . http_build_query(['error' => true, 'error_type' => 'payment']);
            }, 123);
        }

        if (defined('ACTION_AFTER_UPDATE_PAYMENT')) {
            add_action(ACTION_AFTER_UPDATE_PAYMENT, function ($request, $payment) {
                if (in_array($payment->payment_channel, [PaymentMethodEnum::COD, PaymentMethodEnum::BANK_TRANSFER])
                    && $request->input('status') == PaymentStatusEnum::COMPLETED
                ) {
                    $subscribedPackageId = MetaBox::getMetaData($payment, 'subscribed_packaged_id', true);

                    if (! $subscribedPackageId) {
                        return;
                    }

                    $package = app(PackageInterface::class)->findById($subscribedPackageId);

                    if (! $package) {
                        return;
                    }

                    $account = app(AccountInterface::class)->findById($payment->customer_id);

                    if (! $account) {
                        return;
                    }

                    if ($payment->status == PaymentStatusEnum::PENDING) {
                        $account->credits += $package->number_of_listings;
                        $account->save();

                        $account->packages()->attach($package);
                    }

                    $payment->status = PaymentStatusEnum::COMPLETED;

                    Invoice::query()
                        ->where('reference_id', $package->id)
                        ->where('reference_type', Package::class)
                        ->update(['status' => InvoiceStatusEnum::COMPLETED]);
                }
            }, 123, 2);
        }

        if (defined('PAYMENT_FILTER_PAYMENT_DATA')) {
            add_filter(PAYMENT_FILTER_PAYMENT_DATA, function (array $data, Request $request) {
                $orderIds = [session('subscribed_packaged_id')];

                $package = $this->app->make(PackageInterface::class)
                    ->findById(Arr::first($orderIds));

                $discountAmount = 0;

                $couponService = $this->app->make(CouponService::class);

                if (Session::has('applied_coupon_code')) {
                    $coupon = $couponService->getCouponByCode(Session::get('applied_coupon_code'));

                    if ($coupon) {
                        $discountAmount = $couponService->getDiscountAmount(
                            $coupon->type->getValue(),
                            $coupon->value,
                            $package->price
                        );

                        $coupon->increment('total_used');
                    }
                }

                $products = [
                    [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'price_per_order' => $package->price,
                        'qty' => 1,
                    ],
                ];

                $account = auth('account')->user();

                $address = [
                    'name' => $account->name,
                    'email' => $account->email,
                    'phone' => $account->phone,
                    'country' => null,
                    'state' => null,
                    'city' => null,
                    'address' => null,
                    'zip' => null,
                ];

                return [
                    'amount' => $couponService->getAmountAfterDiscount($discountAmount, $package->price),
                    'shipping_amount' => 0,
                    'shipping_method' => null,
                    'tax_amount' => 0,
                    'currency' => strtoupper(get_application_currency()->title),
                    'order_id' => $orderIds,
                    'description' => trans('plugins/payment::payment.payment_description', ['order_id' => Arr::first($orderIds), 'site_url' => request()->getHost()]),
                    'customer_id' => $account->id,
                    'customer_type' => Account::class,
                    'return_url' => $request->input('return_url'),
                    'callback_url' => $request->input('callback_url'),
                    'products' => $products,
                    'orders' => [$package],
                    'address' => $address,
                    'checkout_token' => session('subscribed_packaged_id'),
                ];
            }, 120, 2);
        }

        add_action(BASE_ACTION_META_BOXES, function ($context, $object) {
            if (request()->segment(1) == 'account') {
                MetaBox::removeMetaBox('seo_wrap', Job::class, 'advanced');
            }

            if (get_class($object) == Account::class && $object->isEmployer()) {
                MetaBox::removeMetaBox('seo_wrap', Account::class, 'advanced');
            }
        }, 11, 2);

        add_filter(BASE_FILTER_SLUG_AREA, function (?string $html, $object) {
            if (get_class($object) == Account::class && $object->isEmployer()) {
                return '';
            }

            return $html;
        }, 27, 2);

        if (defined('THEME_FRONT_HEADER')) {
            add_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, function ($screen, $job) {
                add_filter(THEME_FRONT_HEADER, function ($html) use ($job) {
                    if (get_class($job) != Job::class) {
                        return $html;
                    }

                    $expiredAt = Carbon::now()->toDateString();

                    if (! $job->is_expired) {
                        if ($job->expire_date) {
                            $expiredAt = $job->expire_date->toDateString();
                        } else {
                            $expiredAt = Carbon::now()->addDays(JobBoardHelper::jobExpiredDays())->toDateString();
                        }
                    }

                    $schema = [
                        '@context' => 'https://schema.org',
                        '@type' => 'JobPosting',
                        'title' => $job->name,
                        'url' => $job->url,
                        'image' => [
                            '@type' => 'ImageObject',
                            'url' => RvMedia::getImageUrl(theme_option('logo')),
                        ],
                        'description' => BaseHelper::clean($job->content),
                        'employmentType' => implode(', ', $job->jobTypes->pluck('name')->all()),
                        'jobLocation' => [
                            '@type' => 'Place',
                            'address' => [
                                '@type' => 'PostalAddress',
                                'streetAddress' => $job->address,
                                'addressLocality' => $job->city_name . ', ' . $job->state_name,
                                'addressRegion' => $job->state_name,
                                'addressCountry' => $job->country_name,
                            ],
                        ],
                        'hiringOrganization' => [
                            '@type' => 'Organization',
                            'name' => $job->company->name,
                            'url' => $job->company->website,
                            'logo' => [
                                '@type' => 'ImageObject',
                                'url' => RvMedia::getImageUrl($job->company->logo, null, false, theme_option('logo')),
                            ],
                        ],
                        'baseSalary' => [
                            '@type' => 'MonetaryAmount',
                            'currency' => strtoupper(get_application_currency()->title),
                            'minValue' => $job->salary_from,
                            'maxValue' => $job->salary_to,
                            'unitText' => strtoupper($job->salary_range),
                        ],
                        'validThrough' => $expiredAt,
                        'datePosted' => $job->created_at->toDateString(),
                        'datePublished' => $job->created_at->toDateString(),
                        'dateModified' => $job->updated_at->toDateString(),
                    ];

                    return $html . Html::tag('script', json_encode($schema), ['type' => 'application/ld+json'])
                            ->toHtml();
                }, 30);
            }, 30, 2);
        }

        add_filter('account_dashboard_header', function ($html) {
            $customCSSFile = public_path(Theme::path() . '/css/style.integration.css');
            if (File::exists($customCSSFile)) {
                $html .= Html::style(Theme::asset()
                    ->url('css/style.integration.css?v=' . filectime($customCSSFile)));
            }

            return $html . ThemeSupport::getCustomJS('header');
        }, 15);

        if (is_plugin_active('payment')) {
            add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
                $invoice = Invoice::where('payment_id', $payment->id)->first();

                if (! $invoice) {
                    return $data;
                }

                $button = view('plugins/job-board::partials.invoice-buttons', compact('invoice'))->render();

                return $data . $button;
            }, 3, 2);
        }

        if (defined('PAGE_MODULE_SCREEN_NAME')) {
            add_filter(PAGE_FILTER_PAGE_NAME_IN_ADMIN_LIST, [$this, 'addAdditionNameToPageName'], 124, 2);
        }

        add_filter('social_login_before_saving_account', function ($data, $oAuth, $providerData) {
            if (Arr::get($providerData, 'model') == Account::class && Arr::get($providerData, 'guard') == 'account') {
                $firstName = implode(' ', explode(' ', $oAuth->getName(), -1));
                Arr::forget($data, 'name');
                $data = array_merge($data, [
                    'first_name' => $firstName,
                    'last_name' => trim(str_replace($firstName, '', $oAuth->getName())),
                    'type' => '',
                ]);
            }

            return $data;
        }, 49, 3);
    }

    public function countPendingApplications(?string $number, string $menuId): ?string
    {
        if (in_array($menuId, ['cms-plugins-job-board-application', 'cms-plugins-job-board-main'])) {
            $attributes = [
                'class' => 'badge badge-success menu-item-count unread-consults',
                'style' => 'display: none;',
            ];

            return Html::tag('span', '', $attributes)->toHtml();
        }

        return $number;
    }

    public function getMenuItemCount(array $data = []): array
    {
        if (Auth::user()->hasPermission('job-applications.index')) {
            $data[] = [
                'key' => 'unread-consults',
                'value' => $this->app->make(JobApplicationInterface::class)
                    ->count(['status' => JobApplicationStatusEnum::PENDING]),
            ];
        }

        return $data;
    }

    public function addThemeOptions(): void
    {
        $pages = $this->app->make(PageInterface::class)->pluck('name', 'id', ['status' => BaseStatusEnum::PUBLISHED]);

        theme_option()
            ->setSection([
                'title' => trans('plugins/job-board::job-board.theme_options.name'),
                'desc' => trans('plugins/job-board::job-board.theme_options.description'),
                'id' => 'opt-text-subsection-job-board',
                'subsection' => true,
                'icon' => 'fas fa-briefcase',
                'fields' => [
                    [
                        'id' => 'logo_employer_dashboard',
                        'type' => 'mediaImage',
                        'label' => trans('plugins/job-board::job-board.theme_options.logo_employer_dashboard'),
                        'attributes' => [
                            'name' => 'logo_employer_dashboard',
                            'value' => null,
                        ],
                    ],
                    [
                        'id' => 'default_company_cover_image',
                        'type' => 'mediaImage',
                        'label' => trans('plugins/job-board::job-board.theme_options.default_company_cover_image'),
                        'attributes' => [
                            'name' => 'default_company_cover_image',
                            'value' => null,
                        ],
                    ],
                    [
                        'id' => 'default_company_logo',
                        'type' => 'mediaImage',
                        'label' => trans('plugins/job-board::job-board.theme_options.default_company_logo'),
                        'attributes' => [
                            'name' => 'default_company_logo',
                            'value' => null,
                        ],
                    ],
                    [
                        'id' => 'job_companies_page_id',
                        'type' => 'customSelect',
                        'label' => trans('plugins/job-board::job-board.theme_options.job_companies_page_id'),
                        'attributes' => [
                            'name' => 'job_companies_page_id',
                            'list' => ['' => __('-- select --')] + $pages,
                            'value' => '',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                    [
                        'id' => 'job_categories_page_id',
                        'type' => 'customSelect',
                        'label' => trans('plugins/job-board::job-board.theme_options.job_categories_page_id'),
                        'attributes' => [
                            'name' => 'job_categories_page_id',
                            'list' => ['' => __('-- select --')] + $pages,
                            'value' => '',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                    [
                        'id' => 'job_candidates_page_id',
                        'type' => 'customSelect',
                        'label' => trans('plugins/job-board::job-board.theme_options.job_candidates_page_id'),
                        'attributes' => [
                            'name' => 'job_candidates_page_id',
                            'list' => ['' => __('-- select --')] + $pages,
                            'value' => '',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                    [
                        'id' => 'job_list_page_id',
                        'type' => 'customSelect',
                        'label' => trans('plugins/job-board::job-board.theme_options.job_list_page_id'),
                        'attributes' => [
                            'name' => 'job_list_page_id',
                            'list' => ['' => __('-- select --')] + $pages,
                            'value' => '',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                ],
            ])
            ->setField([
                'id' => 'show_map_on_jobs_page',
                'section_id' => 'opt-text-subsection-job-board',
                'type' => 'customSelect',
                'label' => __('Show map on jobs page?'),
                'attributes' => [
                    'name' => 'show_map_on_jobs_page',
                    'list' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'value' => 'yes',
                ],
            ])
            ->setField([
                'id' => 'latitude_longitude_center_on_jobs_page',
                'section_id' => 'opt-text-subsection-job-board',
                'type' => 'text',
                'label' => __('Latitude longitude center on jobs page'),
                'attributes' => [
                    'name' => 'latitude_longitude_center_on_jobs_page',
                    'value' => '43.615134, -76.393186',
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ]);
    }

    public function addAdditionNameToPageName(?string $name, Page $page): ?string
    {
        $subTitle = null;

        switch ($page->id) {
            case theme_option('job_list_page_id'):
                $subTitle = trans('plugins/job-board::job-board.jobs_page');

                break;
            case theme_option('job_categories_page_id'):
                $subTitle = trans('plugins/job-board::job-board.categories_page');

                break;
            case theme_option('job_companies_page_id'):
                $subTitle = trans('plugins/job-board::job-board.companies_page');

                break;
            case theme_option('job_candidates_page_id'):
                $subTitle = trans('plugins/job-board::job-board.candidates_page');

                break;
        }

        if ($subTitle) {
            $subTitle = Html::tag('span', $subTitle, ['class' => 'additional-page-name'])
                ->toHtml();

            if (Str::contains($name, ' —')) {
                return $name . ', ' . $subTitle;
            }

            return $name . ' —' . $subTitle;
        }

        return $name;
    }
}

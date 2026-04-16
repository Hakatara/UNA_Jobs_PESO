<?php

namespace Botble\JobBoard\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Botble\ACL\Traits\RegistersUsers;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Captcha\Facades\Captcha;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Http\Requests\RegisterRequest;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JsValidation\Facades\JsValidator;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected string $redirectTo = '/';

    public function __construct(protected AccountInterface $accountRepository)
    {
    }

    public function showRegistrationForm()
    {
        SeoHelper::setTitle(__('Register'));

        Theme::breadcrumb()->add(__('Home'), route('public.index'))
            ->add(__('Register'), route('public.account.register'));

        Theme::asset()->container('footer')
            ->add('js-validation', 'vendor/core/core/js-validation/js/js-validation.js', ['jquery']);
        Theme::asset()->container('footer')->writeContent(
            'js-validation-scripts',
            JsValidator::formRequest(RegisterRequest::class),
            ['jquery']
        );

        if (! session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }

        return Theme::scope('job-board.auth.register', [], 'plugins/job-board::themes.auth.register')->render();
    }

    public function confirm(
        $email,
        Request $request,
        BaseHttpResponse $response,
        AccountInterface $accountRepository
    ) {
        if (! URL::hasValidSignature($request)) {
            abort(404);
        }

        $account = $accountRepository->getFirstBy(['email' => $email]);

        if (! $account) {
            abort(404);
        }

        $account->confirmed_at = Carbon::now();
        $this->accountRepository->createOrUpdate($account);

        $this->guard()->login($account);

        return $response
            ->setNextUrl(route('public.account.dashboard'))
            ->setMessage(trans('plugins/job-board::account.confirmation_successful'));
    }

    protected function guard()
    {
        return auth('account');
    }

    public function resendConfirmation(
        Request $request,
        AccountInterface $accountRepository,
        BaseHttpResponse $response
    ) {
        $account = $accountRepository->getFirstBy(['email' => $request->input('email')]);
        if (! $account) {
            return $response
                ->setError()
                ->setMessage(__('Cannot find this account!'));
        }

        try {
            $account->sendEmailVerificationNotification();
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }

        return $response
            ->setMessage(trans('plugins/job-board::account.confirmation_resent'));
    }

    public function register(Request $request, BaseHttpResponse $response)
    {
        if ($request->input('is_employer') && setting('job_board_enabled_register_as_employer', 1)) {
            $request->merge(['type' => AccountTypeEnum::EMPLOYER]);
        } else {
            $request->merge(['type' => AccountTypeEnum::JOB_SEEKER]);
        }

        $this->validator($request->input())->validate();

        $account = $this->create($request->input());
        event(new Registered($account));

        $request->merge(['slug' => $account->name, 'is_slug_editable' => 1]);

        event(new CreatedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

        EmailHandler::setModule(JOB_BOARD_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'account_type' => Str::lower($account->type->label()),
                'account_name' => $account->name,
                'account_email' => $account->email,
            ])
            ->sendUsingTemplate('account-registered', setting('email_from_address'));

        if (setting('verify_account_email', 0)) {
            $account->sendEmailVerificationNotification();

            $this->registered($request, $account);

            return $response->setNextUrl($this->redirectPath())->setMessage(
                trans('plugins/job-board::account.confirmation_info')
            );
        }

        $this->guard()->login($account);

        $this->registered($request, $account);

        return $response->setNextUrl($this->redirectPath());
    }

    protected function create(array $data)
    {
        return $this->accountRepository->create([
            'type' => $data['type'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    protected function validator(array $data)
    {
        $rules = [
            'first_name' => 'required|max:120|min:2',
            'last_name' => 'required|max:120|min:2',
            'email' => 'required|max:60|min:6|email|unique:jb_accounts',
            'password' => 'required|min:6|confirmed',
        ];

        if (is_plugin_active('captcha') && setting('enable_captcha') && setting(
            'job_board_enable_recaptcha_in_register_page',
            0
        )) {
            $rules += Captcha::rules();
        }

        if (request()->has('agree_terms_and_policy')) {
            $rules['agree_terms_and_policy'] = 'accepted:1';
        }

        $attributes = array_merge([
            'first_name' => __('First Name'),
            'last_name' => __('Last Name'),
            'email' => __('Email'),
            'password' => __('Password'),
            'agree_terms_and_policy' => __('Term and Policy'),
        ], Captcha::attributes());

        return Validator::make($data, $rules, [], $attributes);
    }
}

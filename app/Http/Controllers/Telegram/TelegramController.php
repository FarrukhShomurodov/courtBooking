<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Services\OtpService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramController extends Controller
{
    protected Api $telegram;
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        $this->telegram = new Api(config('telegram.bot_token'));
    }

    public function handleWebhook(): void
    {
        $update = $this->telegram->getWebhookUpdates();
        $chatId = $update->getMessage()->getChat()->getId();
        $text = $update->getMessage()->getText();

        // Retrieve or create a BotUser instance
        $user = BotUser::query()->firstOrCreate(['chat_id' => $chatId], ['isactive' => true]);

        if (!$user->isactive) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => __('telegram.user_isnt_active'),
            ]);
            return;
        }

        $botUserInfo = $update->getMessage();
        if (isset($user->lang)) {
            Session::put('locale', $user->lang);
            App::setLocale($user->lang);
        }

        if ($text === '/start') {
            $user->step = 'LANG_SELECTION';
            $user->save();
        }

        if (empty($user->first_name) || empty($user->second_name) || empty($user->uname)) {
            $user->first_name = $botUserInfo->from->first_name;
            $user->second_name = $botUserInfo->from->last_name;
            $user->uname = $botUserInfo->from->username;
            $user->save();
        }

        if ($update->getMessage()->has('contact')) {
            $phoneNumber = $update->getMessage()->getContact()->getPhoneNumber();

            if ($user->step !== 'CHANGE_PHONE') {
                $user->step = 'VERIFY_PHONE';
                $user->save();
            }

            $user->phone = $phoneNumber;
            $user->save();

            $this->sendOtp($chatId, $phoneNumber, $user);
            return;
        }

        if ($user->step === 'VERIFY_PHONE' || $user->step === 'CHANGE_PHONE') {
            if ($text !== __('telegram.resend_phone_number')) {
                $this->verifyOtp($chatId, $text, $user);
            } else {
                $this->sendOtp($chatId, $user->phone, $user);
            }
            return;
        }


        switch ($text) {
            case '/start':
                $this->sendWelcomeMessage($chatId);
                $this->sendLangSelection($chatId);
                $user->step = 'LANG_SELECTION';
                $user->save();
                break;
            case 'Русский':
            case "O'zbekcha":
                $locale = $text === 'Русский' ? 'ru' : 'uz';
                Session::put('locale', $locale);
                App::setLocale($locale);
                $user->lang = $locale;
                $user->save();

                if ($user->step === 'CHANGE_LANGUAGE') {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => __('telegram.lang_change_success'),
                    ]);
                    $user->step = 'SETTINGS';
                    $user->save();
                    $this->sendSettings($chatId, $user);
                } else {
                    $user->step = 'PHONE_REQUEST';
                    $user->save();
                    $this->requestPhoneNumber($chatId);
                }
                break;
            case __('telegram.resend_phone_number'):
                $user->step = 'PHONE_REQUEST';
                $user->save();
                $this->requestPhoneNumber($chatId);
                break;
            case __('telegram.support_connect'):
                $user->step = 'SUPPORT';
                $user->save();
                $this->sendSupportData($chatId, $user);
                break;
            case __('telegram.settings_in_menu'):
                $this->sendSettings($chatId, $user);
                break;
            case __('telegram.language'):
                $user->step = 'CHANGE_LANGUAGE';
                $user->save();
                $this->sendLangSelection($chatId);
                break;
            case __('telegram.back'):
                $this->backTo($chatId, $user);
                break;
            case __('telegram.phone'):
                $user->step = 'CHANGE_PHONE';
                $user->save();
                $this->requestPhoneNumber($chatId);
                break;
            case __('telegram.name'):
                $user->step = 'CHANGE_NAME';
                $user->save();
                $this->requestNewName($chatId);
                break;
            case __('telegram.order_btn'):
                $keyboard = [
                    [
                        ['text' => 'webapp', 'web_app' => ['url' => env('APP_URL') . '/telegram/webapp?lang=' . $user->lang]],
                    ]
                ];

                $reply_markup = Keyboard::make([
                    'inline_keyboard' => $keyboard
                ]);

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => __('telegram.order_btn'),
                    'reply_markup' => $reply_markup,
                ]);
            default:
                if ($user->step === 'CHANGE_NAME') {
                    $this->saveNewName($chatId, $text, $user);
                } else {
                    $this->sendMainMenu($chatId, $user);
                }
                break;
        }

        if ($update->has('callback_query')) {
            $callbackQuery = $update->getCallbackQuery();
            $data = $callbackQuery->getData();

            switch ($data) {
                case 'accept_offer':
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => __('telegram.auth_success')
                    ]);
                    $this->sendMainMenu($chatId, $user);
                    break;
                case 'change_language_ru':
                    Session::put('locale', 'ru');
                    App::setLocale('ru');

                    $user->lang = 'ru';
                    $user->save();
                    $this->changeLanguage($chatId, 'Русский', $user);
                    break;
                case 'change_language_uz':
                    Session::put('locale', 'uz');
                    App::setLocale('uz');

                    $user->lang = 'uz';
                    $user->save();
                    $this->changeLanguage($chatId, "O'zbekcha", $user);
                    break;
                case 'change_phone':
                    $user->step = 'CHANGE_PHONE';
                    $user->save();
                    $this->requestNewPhoneNumber($chatId);
                    break;
                case 'change_name':
                    $user->step = 'CHANGE_NAME';
                    $user->save();
                    $this->requestNewName($chatId);
                    break;

            }
        }
    }

    protected function sendSupportData($chatId, $user): void
    {
        $user->step = 'MAIN_MENU';
        $user->save();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.support')
        ]);

        $this->sendMenu($chatId, $user);
    }

    protected function backTo($chatId, $user)
    {
        switch ($user->step) {
            case 'LANG_SELECTION':
                $user->step = 'MAIN_MENU';
                break;
            case 'CHANGE_LANGUAGE':
            case 'CHANGE_PHONE':
            case 'CHANGE_NAME':
                $user->step = 'SETTINGS';
                break;
            case 'SETTINGS':
                $user->step = 'MAIN_MENU';
                break;
        }
        $user->save();
        $this->sendMenu($chatId, $user);
    }

    protected function sendMenu($chatId, $user): void
    {
        switch ($user->step) {
            case 'MAIN_MENU':
                $this->sendMainMenu($chatId, $user);
                break;
            case 'SETTINGS':
                $this->sendSettings($chatId, $user);
                break;
        }
    }

    protected function sendWelcomeMessage($chatId): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Добро пожаловать! \n\n Xush kelibsiz!",
            'reply_markup' => json_encode([
                'remove_keyboard' => true
            ])
        ]);
    }

    protected function sendLangSelection($chatId): void
    {
        $keyboard = [
            ['Русский', "O'zbekcha"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Пожалуйста, выберите язык.\n\nIltimos, tilni tanlang.",
            'reply_markup' => $reply_markup
        ]);
    }

    protected function requestPhoneNumber($chatId): void
    {
        $keyboard = [
            [
                [
                    'text' => __('telegram.send_phone_btn'),
                    'request_contact' => true
                ]
            ]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.send_phone_mes'),
            'reply_markup' => $reply_markup
        ]);
    }

    protected function sendOtp($chatId, $phoneNumber, $user): void
    {
        $otp = $this->generateOtp();

        $otpTEXT = 'Код подтверждения для регистрации в Telegram-боте FindzBot: ' . $otp;
        $this->otpService->sendMessage(str_replace('+', '', $phoneNumber), $otpTEXT);

        $user->sms_code = $otp;
        $user->save();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.auth_code_mes')
        ]);

        $keyboard = [
            [__('telegram.resend_phone_number')]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'callback_data' => 'resendOtp'
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.auth_code_resend_mes'),
            'reply_markup' => $reply_markup
        ]);
    }

    protected function verifyOtp($chatId, $otp, $user): void
    {
        $cachedOtp = $user->sms_code;
        if ($otp == $cachedOtp) {
            if ($user->step === 'CHANGE_PHONE') {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => __('telegram.phone_successfully_changed')
                ]);
                $user->step = 'SETTINGS';
                $user->save();
                $this->sendSettings($chatId, $user);
            } else {
//                $user->isactive = true;
                $user->step = 'OFFER';
                $user->save();
                $this->sendOffer($chatId);
            }
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => __('telegram.incorrect_code')
            ]);
        }
    }

    protected function sendOffer($chatId): void
    {
        $keyboard = [
            [
                ['text' => __('telegram.offerta_btn'), 'url' => 'https://example.com/offer'],
                ['text' => __('telegram.access_btn'), 'callback_data' => 'accept_offer']
            ]
        ];

        $reply_markup = Keyboard::make([
            'inline_keyboard' => $keyboard
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.offerta_mes'),
            'reply_markup' => $reply_markup,
        ]);
    }

    protected function sendMainMenu($chatId, $user): void
    {
        $keyboard = [
            [
                [
                    'text' => __('telegram.order_btn'),
                ],
                __('telegram.my_order_btn')],

            [__('telegram.settings_in_menu')]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.main_menu_mes'),
            'reply_markup' => $reply_markup
        ]);
    }

    protected function sendSettings($chatId, $user): void
    {
        $keyboard = [
            [__('telegram.language'), __('telegram.phone'), __('telegram.name')],
            [__('telegram.back')]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $lang = $user->lang == 'ru' ? 'Русский' : "O'zbekcha";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.settings') . PHP_EOL .
                __('telegram.language') . ': ' . $lang . PHP_EOL .
                __('telegram.name') . ': ' . $user->first_name . ' ' . $user->second_name . PHP_EOL .
                __('telegram.phone') . ': ' . $user->phone,
            'reply_markup' => $reply_markup
        ]);
    }

    protected function changeLanguage($chatId, $language, $user): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.language_changed', ['language' => ' ' . $language]),
        ]);

        $this->sendSettings($chatId, $user);
    }

    protected function requestNewPhoneNumber($chatId): void
    {
        $keyboard = [
            [
                [
                    'text' => __('telegram.send_phone_btn'),
                    'request_contact' => true
                ]
            ],
            [__('telegram.back')]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.send_phone_mes'),
            'reply_markup' => $reply_markup
        ]);
    }

    protected function requestNewName($chatId): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.send_new_name')
        ]);
    }

    protected function generateOtp(): string
    {
        return (string)random_int(1000, 9999);
    }

    protected function saveNewName($chatId, $newName, $user): void
    {
        $user->first_name = $newName;
        $user->step = 'MAIN_MENU';
        $user->save();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('telegram.name_successfully_changed')
        ]);

        $this->sendSettings($chatId, $user);

    }
}

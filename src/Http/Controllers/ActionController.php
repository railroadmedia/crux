<?php

namespace Railroad\Crux\Http\Controllers;

use Illuminate\Http\RedirectResponse as RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Railroad\Crux\Mail\Agnostic;
use Railroad\Crux\Services\BrandSpecificResourceService;

class ActionController
{
    public static $internalEmailRecipientsByBrand = [
        'drumeo' => ['support@drumeo.com'],
        'pianote' => ['support@pianote.com'],
        'guitareo' => ['support@guitareo.com'],
        'singeo' => ['support@singeo.com'],
    ];

    public static $generalSuccessMessageToUser = 'Your account has been updated.';

    // todo: use this? Ask marketing|UX|Owner for alternate phrasing?
    public static $generalErrorMessageToUser = 'We\'re sorry, but there\'s been an error. Please reload the page and try ' .
    'again. If that doesn\'t work email or use the chat at the bottom right of your screen to get things sorted out ' .
    'right away.';

    /**
     * @param bool $success
     * @param null $msg
     * @param string $route
     * @return RedirectResponse
     */
    private function returnRedirect($success = true, $msg = null, $route = 'crux.access-details')
    {
        $type = $success ? 'success-message' : 'error-message';

        $msg = $msg ?? $success ? self::$generalSuccessMessageToUser : self::$generalErrorMessageToUser;

        return redirect()->route($route)->with([$type => $msg]);
    }

    public function acceptTrialExtensionOffer()
    {

    }

    public function acceptMonthExtensionOffer()
    {

    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendHelpEmail(Request $request)
    {
        $brand = config('railcontent.brand');

        $userEmail = current_user()->getEmail();

        if (!empty($request->get('email'))) {
            $apparentlyValidEmail = filter_var($request->get('email'), FILTER_VALIDATE_EMAIL);
            if ($apparentlyValidEmail) {
                $userEmail = $request->get('email');
            }
        }

        $optionToLabelMap = [
            'direction' => 'I need more direction',
            'time' => 'I don’t have enough time',
            'watch' => 'I don’t know what lesson to watch',
            'easy' => 'The lessons are too easy',
            'difficult' => 'The lessons are too difficult',
            'website' => 'I don’t know how to use the website/app.',
            'other' => 'Other',
        ];

        $optionDescription = $optionToLabelMap[$request->get('help-issue')] ?? $request->get('help-issue');
        $textInput = $request->get('text-input') ?? '';

        try {
            $email = new Agnostic();

            $email->to(self::$internalEmailRecipientsByBrand[$brand] ?? 'support@musora.com');
            $email->from('system@drumeo.com', 'Drumeo System');
            $email->replyTo(current_user()->getEmail());

            $email->subject('Request for help making most of membership: ' . current_user()->getEmail());
            $email->view('emails.help-from-cancellation-flow');
            $email->with([
                'optionDescription' => $optionDescription,
                'textInput' => $textInput,
                'userEmail' => $userEmail,
                'userId' => current_user()->getId(),
                'userDisplayName' => current_user()->getDisplayName(),
                'brandLogo' => BrandSpecificResourceService::logoUrl($brand)
            ]);

            Mail::send($email);
            $success = true;
        } catch (\Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return $this->returnRedirect($success, "Your request has sent. Expect a reply soon!");
    }

    public function submitFeedback()
    {

    }

    public function submitCancelReason()
    {
        dd('submitCancelReason!!');
    }
}
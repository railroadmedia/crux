<?php

namespace Railroad\Crux\Http\Controllers;

use App\Maps\ProductAccessMap;
use App\Services\User\UserAccessService;
use Carbon\Carbon;
use Doctrine\ORM\ORMException;
use Illuminate\Http\RedirectResponse as RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Railroad\Crux\Mail\Agnostic;
use Railroad\Crux\Services\BrandSpecificResourceService;
use Railroad\Ecommerce\Entities\MembershipAction;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\Traits\NotableEntity;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Entities\UserProduct;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionUpdated;
use Railroad\Ecommerce\Managers\EcommerceEntityManager;
use Railroad\Ecommerce\Repositories\MembershipActionRepository;
use Railroad\Ecommerce\Repositories\OrderRepository;
use Railroad\Ecommerce\Repositories\ProductRepository;
use Railroad\Ecommerce\Repositories\SubscriptionRepository;
use Railroad\Ecommerce\Services\UserProductService;
use Railroad\Mailora\Services\MailService;

class ActionController
{
    private $brand;

    /**
     * @var EcommerceEntityManager
     */
    private $ecommerceEntityManager;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var UserProductService
     */
    private $userProductService;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var MembershipActionRepository
     */
    private $membershipActionRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;


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
     * CancellationController constructor.
     * @param SubscriptionRepository $subscriptionRepository
     * @param EcommerceEntityManager $ecommerceEntityManager
     * @param UserProductService $userProductService
     * @param ProductRepository $productRepository
     * @param MailService $mailService
     * @param MembershipActionRepository $membershipActionRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        EcommerceEntityManager $ecommerceEntityManager,
        UserProductService $userProductService,
        ProductRepository $productRepository,
        MailService $mailService,
        MembershipActionRepository $membershipActionRepository,
        OrderRepository $orderRepository
    )
    {
        $this->ecommerceEntityManager = $ecommerceEntityManager;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userProductService = $userProductService;
        $this->productRepository = $productRepository;
        $this->mailService = $mailService;
        $this->membershipActionRepository = $membershipActionRepository;
        $this->orderRepository = $orderRepository;

        $this->brand = config('railcontent.brand');
    }

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
        $targetProductIdsByBrand = [
            'drumeo' => [126, 238],
            'pianote' => [],
            'guitareo' => [],
            'singeo' => [],
        ];

        // these are the 7 day and 30 day trial product ids
        $subscription = $this->updateSubscriptionPaidUntilDate($targetProductIdsByBrand[$this->brand], 'addDays', 14);

        if (!$subscription) {
            return redirect()->route('crux.access-details')
                ->with(
                    [
                        'error-message' => 'Whoops, something went wrong when we tried to extend your trial. Please try again or contact our support team.'
                    ]
                );
        }

        $oldSubscription = clone $subscription;
        event(new SubscriptionUpdated($oldSubscription, $subscription));

        // save membership action
        $membershipAction = new MembershipAction(); /** @var $membershipAction MembershipAction|NotableEntity */
        $membershipAction->setUser(new User(current_user()->getId(), current_user()->getEmail()));
        $membershipAction->setBrand(config('ecommerce.brand'));
        $membershipAction->setAction(MembershipAction::ACTION_EXTEND_FOR_AMOUNT_OF_DAYS);
        $membershipAction->setActionAmount(14);
        $membershipAction->setSubscription($subscription);
        $membershipAction->setNote('trial was extended by 14 days');

        try {
            $this->ecommerceEntityManager->persist($membershipAction);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return redirect()->route('crux.access-details')
            ->with(
                [
                    'success-message' => 'Your trial has successfully been extended 14 days. Your new renewal date is: ' .
                        $subscription->getPaidUntil()->format('F j, Y')
                ]
            );
    }

    /**
     * This extends the users active subscription and access by 30 days.
     *
     * @return RedirectResponse
     */
    public function acceptMonthExtensionOffer()
    {
        $targetProductIdsByBrand = [
            'drumeo' => [124, 125, 126],
            'pianote' => [],
            'guitareo' => [],
            'singeo' => [],
        ];

        $membershipProductIds = ProductAccessMap::membershipProductIds();

        // these are the yearly, monthly, and trial-with-renewal edge subscription product ids
        $subscription = $this->updateSubscriptionPaidUntilDate(
            $targetProductIdsByBrand[$this->brand],
            'addDays',
            30
        );

        if (!$subscription) {
            return redirect()->route('crux.access-details')
                ->with(
                    [
                        'error-message' => 'Whoops, something went wrong when we tried to extend your membership. ' .
                            'Please try again or contact our support team.'
                    ]
                );
        }

        $oldSubscription = clone $subscription;
        event(new SubscriptionUpdated($oldSubscription, $subscription));

        // save membership action
        $membershipAction = new MembershipAction(); /** @var $membershipAction MembershipAction|NotableEntity */
        $membershipAction->setUser(new User(current_user()->getId(), current_user()->getEmail()));
        $membershipAction->setBrand($this->brand);
        $membershipAction->setAction(MembershipAction::ACTION_EXTEND_FOR_AMOUNT_OF_DAYS);
        $membershipAction->setActionAmount(30);
        $membershipAction->setSubscription($subscription);
        $membershipAction->setNote('membership was extended by 30 days');

        try {
            $this->ecommerceEntityManager->persist($membershipAction);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return redirect()->route('crux.access-details', ['open-modal-id' => 'modal-how-can-we-make-next-30-days-better'])
            ->with(
                [
                    'success-message' => 'Your membership has been extended by 30 days successfully! Your new ' .
                        'renewal date is: ' . $subscription->getPaidUntil()->format('F j, Y'),
                    'renewal-date' => $subscription->getPaidUntil()->format('F j, Y')
                ]
            );
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendHelpEmail(Request $request)
    {
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

            $email->to(self::$internalEmailRecipientsByBrand[$this->brand] ?? 'support@musora.com');
            $email->from('system@drumeo.com', 'Drumeo System');
            $email->replyTo(current_user()->getEmail());

            $email->subject('Request for help making most of membership: ' . current_user()->getEmail());
            $email->view('crux::email.help-from-cancellation-flow');
            $email->with([
                'optionDescription' => $optionDescription,
                'textInput' => $textInput,
                'userEmail' => $userEmail,
                'userId' => current_user()->getId(),
                'userDisplayName' => current_user()->getDisplayName(),
                'brandLogo' => BrandSpecificResourceService::logoUrl($this->brand)
            ]);

            Mail::send($email);
            $success = true;
        } catch (\Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return $this->returnRedirect($success, "Your request has sent. Expect a reply soon!");
    }

    public function submitFeedback(Request $request)
    {
        $userFeedback = $request->get('user-feedback');

        $acceptedMonthExtensionOffer = $request->get('accepted-month-extension-offer') ?? false;
        if($acceptedMonthExtensionOffer){
            $view = 'crux::email.feedback-with-cancellation-extension-offer';
            $subject = 'User feedback to make the next 30 days better, from ' . current_user()->getEmail();
            $renewalDate = $request->get('renewal-date');
            if($renewalDate){
                $successMessage = 'Your feedback has been submitted, and your membership has been extended by 30 ' .
                    'days! Your new renewal date is: ' . $renewalDate;
            }else{
                $successMessage = 'Your feedback has been submitted, and your membership has been extended by 30 ' .
                    'days!';
            }
        }else{
            $view = 'crux::email.feedback-from-cancellation-workflow';
            $subject = 'User feedback to improve their ' . ucfirst($this->brand) . ' Experience, from ' . current_user()->getEmail();
        }

        try {
            $email = new Agnostic();

            $email->from('system@drumeo.com', 'Drumeo System');
            $email->to(self::$internalEmailRecipientsByBrand[$this->brand]);
            $email->replyTo(current_user()->getEmail());

            $email->subject($subject);
            $email->view($view);
            $email->with([
                'userFeedback' => $userFeedback,
                'userEmail' => current_user()->getEmail(),
                'userId' => current_user()->getId(),
                'userDisplayName' => current_user()->getDisplayName(),
                'brand' => $this->brand,
                'brandLogo' => BrandSpecificResourceService::logoUrl($this->brand),
            ]);

            Mail::send($email);

        } catch (\Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return redirect()->route('crux.access-details')->with([
            'success-message' => $successMessage ?? 'Your feedback has been submitted, thank you!'
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function submitCancelReason(Request $request)
    {
        /* use case map (inputName => where to go):
         *
         * "no-time"                => Special Offer // Pause Membership
         * "too-expensive"          => Special Offer // Pause Membership
         * "dont-use-enough"        => Special Offer // Pause Membership
         * "couldnt-find-lessons"   => Student Care
         * "too-easy"               => Student Care
         * "too-difficult"          => Student Care
         * "lesson-quality"         => Student Care
         * "dont-like-website"      => Student Care
         * "technical-problems"     => Student Care
         * "other-drum-lessons"     => Special Offer // Pause Membership
         * "other-reason"           => just cancel and redirect to account details
         */

        $reason = $request->get('reason');
        $textReason = $request->get('other-reason-text');
        $subscription = UserAccessService::getMembershipSubscription(
            current_user()->getId()
        );
        $isTrial = in_array($subscription->getProduct()->getId(), ProductAccessMap::trialMembershipProductIds());

        // store in session
        session()->put($this->brand . '-cancel-reason', $reason);
        session()->put($this->brand . '-cancel-reason-text', $textReason);

        if (UserAccessService::hasClaimedRetentionOfferWithin(6) || $isTrial) {
            return $this->cancel($request);
        }

        $criteria = [
            config('crux.reasonsByBrand')[$this->brand]['no-time'],
            config('crux.reasonsByBrand')[$this->brand]['too-expensive'],
            config('crux.reasonsByBrand')[$this->brand]['dont-use-enough'],
            config('crux.reasonsByBrand')[$this->brand]['other-drum-lessons'],
        ];

        $match = in_array($reason, $criteria);

        // send to final offer screen depending on use case
        if ($match) {
            if ($subscription->getIntervalType() == config('ecommerce.interval_type_monthly')) {
                return redirect()->route('user.settings.cancel.monthly-offer');
            } else {
                return redirect()->route('user.settings.cancel.annual-offer');
            }
        }

        $criteria = [
            config('crux.reasonsByBrand')[$this->brand]['couldnt-find-lessons'],
            config('crux.reasonsByBrand')[$this->brand]['too-easy'],
            config('crux.reasonsByBrand')[$this->brand]['too-difficult'],
            config('crux.reasonsByBrand')[$this->brand]['lesson-quality'],
            config('crux.reasonsByBrand')[$this->brand]['dont-like-website'],
            config('crux.reasonsByBrand')[$this->brand]['technical-problems'],
        ];

        $match = in_array($reason, $criteria);

        if ($match) {
            return redirect()->route('user.settings.cancel.student-care');
        }

        // other reason
        return $this->cancel($request);
    }

    /**
     * @param $targetProductIds []
     * @param string $carbonMethodName
     * @param string|int $carbonMethodParamValue
     * @return Subscription|boolean
     */
    private function updateSubscriptionPaidUntilDate($targetProductIds, $carbonMethodName, $carbonMethodParamValue)
    {
        $user = current_user();
        $userId = $user->getId();

        try {
            /** @var UserProduct[] $userProducts */
            $userSubscriptions = $this->subscriptionRepository->getSubscriptionsForUsers([$userId]);
        } catch (\Exception $e) {
            error_log($e);
            return false;
        }

        foreach ($userSubscriptions as $subscription) {
            $product = $subscription->getProduct();
            if (in_array($product->getId(), $targetProductIds)) {
                /** @var Carbon $paidUntil */
                $paidUntil = $subscription->getPaidUntil();
                $isExpired = $paidUntil->lt(Carbon::now());
                if ($isExpired) {
                    continue;
                }
                if (isset($subscriptionToUpdate)) {
                    error_log(
                        'user has multiple subscriptions that qualify for updating in \App\Http\Controllers\Profiles\\' .
                        'CancellationController::updateSubscriptionPaidUntilDate. This is may not be desired. Consider ' .
                        'reviewing subscriptions belonging to user ' . $userId . ' to see if there might be an error ' .
                        'somewhere that uses this method.'
                    );
                }
                $subscriptionToUpdate = $subscription;
            }
        }

        if (empty($subscriptionToUpdate)) {
            error_log(
                'No subscriptions attached that matches expectations for this function (user id: ' . $userId . ')'
            );
            return false;
        }

        // you're getting the carbon object that is set as an attribute on the entity, not a copy of the carbon object
        $paidUntil = $subscriptionToUpdate->getPaidUntil();

        try {
            /** @var Carbon $extendedPaidUntil */
            $extendedPaidUntil = $paidUntil->$carbonMethodName($carbonMethodParamValue);
        } catch (\Exception $e) {
            error_log($e);
            return false;
        }

        try {
            $oldSubscriptionToUpdate = clone($subscriptionToUpdate);

            /*
             * NOTE: "copy()" to get new obj else Doctrine won't detect change in Subscription entity (Doctrine doesn't
             * parse obj details, only evaluates whether object is same object of whole different instance)
             */
            $subscriptionToUpdate->setPaidUntil($extendedPaidUntil->copy());

            $this->ecommerceEntityManager->persist($subscriptionToUpdate);
            $this->ecommerceEntityManager->flush();

            event(new SubscriptionUpdated($oldSubscriptionToUpdate, $subscriptionToUpdate));

        } catch (\Throwable $e) {
            error_log($e);
            return false;
        }

        try {
            $this->userProductService->updateSubscriptionProducts($subscriptionToUpdate);
        } catch (\Throwable $e) {
            error_log($e);
            return false;
        }

        return $subscriptionToUpdate;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancel(Request $request)
    {
        $subscription = UserAccessService::getMembershipSubscription(current_user()->getId());
        $oldSubscription = clone $subscription;

        if (empty($subscription)) {
            return redirect()->route('crux.access-details')->with(
                [
                    'error-message' => 'Whoops, something went wrong when we tried to cancel your membership. Please ' .
                        'try again or contact our support team.'
                ]
            );
        }

        $cancelReason = session($this->brand . '-cancel-reason');
        $cancelReasonText = session($this->brand . '-cancel-reason-text');

        $subscription->setCanceledOn(Carbon::now());
        $subscription->setIsActive(false);
        $subscription->setCancellationReason($cancelReason);

        try {
            $this->ecommerceEntityManager->persist($subscription);
            $this->ecommerceEntityManager->flush();

            $this->userProductService->updateSubscriptionProducts($subscription);
        } catch (\Exception|\Throwable $e) {
            return $this->returnRedirect(false);
        }

        event(new SubscriptionUpdated($oldSubscription, $subscription));

        $paidUntilRoundedUp = Carbon::parse($subscription->getPaidUntil()->format('Y-m-d'))->endOfDay();

        // if trial with no payments made revoke access immediately
        $isTrial = in_array($subscription->getProduct()->getId(), ProductAccessMap::trialMembershipProductIds());
        $noPaymentsMade = count($subscription->getPayments()) == 0;
        $revokeAccessImmediately = $isTrial && $noPaymentsMade;

        if ($revokeAccessImmediately) $paidUntilRoundedUp = Carbon::now();

        // ------------------------------------------ PART TWO: send email(s) ------------------------------------------

        // Part 2.1 - email to internal

        $detailedUserInfo = $this->detailedUserInfo($subscription);

        $subject = 'Cancellation notice: ' . current_user()->getEmail();

        try {
            $email = new Agnostic();

            $email->to(self::$internalEmailRecipientsByBrand[$this->brand]);
            $email->from('system@' . $this->brand . '.com', ucfirst($this->brand) . ' System');
            $email->replyTo(current_user()->getEmail());

            $email->subject($subject);
            $email->view('crux::email.cancellation-notice.to-internal');
            $email->with([
                'subject' => $subject,
                'user' => current_user(),
                'selection' => $cancelReason,
                'textInput' => $cancelReasonText,
                'detailedUserInfo' => $detailedUserInfo,
                'brandLogo' => BrandSpecificResourceService::logoUrl($this->brand),
                'brand' => $this->brand,
            ]);

            Mail::send($email);

        } catch (\Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        // Part 2.2 - email to user

        $cancellationSuccessMessage = 'Your membership has been cancelled. You will no longer be automatically ' .
            'billed and your access will end ' . Carbon::parse($paidUntilRoundedUp)->format('l F jS');

        if($revokeAccessImmediately){
            $cancellationSuccessMessage = 'Your membership has been cancelled. You will no longer be automatically billed.';
        }

        try {
            // from support, wants to cancel now
            $email = new Agnostic();

            $email->to(current_user()->getEmail());
            $email->from('team@' . $this->brand . '.com', ucfirst($this->brand) . ' Support');
            $email->replyTo(current_user()->getEmail());

            $subject = '[Important] Your cancellation request has been received.';
            $email->subject($subject);
            $email->view('crux::email.cancellation-notice.to-user');
            $email->replyTo('team@' . $this->brand . '.com');
            $email->with([
//                'subject' => $subject,
//                'user' => current_user(),
//                'selection' => $cancelReason,
//                'textInput' => $cancelReasonText,
//                'detailedUserInfo' => $detailedUserInfo,
//                'brandLogo' => BrandSpecificResourceService::logoUrl($this->brand),
                'brand' => $this->brand,
            ]);

            Mail::send($email);


        } catch (\Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        // save membership action
        $membershipAction = new MembershipAction(); /** @var $membershipAction MembershipAction|NotableEntity */
        $membershipAction->setUser(new User(current_user()->getId(), current_user()->getEmail()));
        $membershipAction->setBrand(config('ecommerce.brand'));
        $membershipAction->setAction(MembershipAction::ACTION_CANCELLED);
        $membershipAction->setActionReason($cancelReason);
        $membershipAction->setSubscription($subscription);
        $membershipAction->setNote($cancelReasonText);

        try {
            $this->ecommerceEntityManager->persist($membershipAction);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        session()->remove($this->brand . '-cancel-reason');
        session()->remove($this->brand . '-cancel-reason-text');

        // respond
        return redirect()->route('crux.access-details')
            ->with(
                [
                    'error-message' => $cancellationSuccessMessage
                ]
            );
    }

    /**
     * @param Subscription $subscription
     * @return array
     */
    private function detailedUserInfo(Subscription $subscription)
    {
        try{
            $subscriptions = $this->subscriptionRepository->getAllUsersSubscriptions(current_user()->getId());

            try {
                $userProducts = $this->userProductService->getAllUsersProducts(current_user()->getId());
            } catch (ORMException $e) {
                error_log($e);
            }

            $i = 1;
            foreach($userProducts ?? [] as $userProduct){
                $product = $userProduct->getProduct();
                $key = 'User product ' . $i . ' of ' . count($userProducts ?? []);

                $expirationDate = 'n/a';
                if($userProduct->getExpirationDate()){
                    $expirationDate = $userProduct->getExpirationDate()->format('M jS Y');
                }

                $userInfo[$key] = $product->getName() . ' (sku: ' . $product->getSku() . ', expiry-date: ' . $expirationDate . ')';
                $i++;
            }

            $ecomUser = new User(current_user()->getId(), '');
            $orders = $this->orderRepository->findBy(['user' => $ecomUser]);

            $numberOfPaymentsForAllSubscriptionsEver = 0;
            /** @var Subscription $_subscription */
            foreach($subscriptions as $_subscription){
                $numberOfPaymentsForAllSubscriptionsEver += count($_subscription->getPayments());
            }

            $userInfo['Number of orders'] = count($orders);
            $userInfo['Number of payments for all subscriptions ever'] = $numberOfPaymentsForAllSubscriptionsEver;

        }catch(\Exception $e){
            error_log($e);
            $userInfo['error'] = $e->getMessage();
        }

        try{
            if(empty($subscription)){
                error_log('Failed to retrieve subscription for member . ' . current_user()->getId());
            }

            $subscriptionInfo['Created on'] = $subscription->getCreatedAt()->format('l F jS\\, Y');
            $subscriptionInfo['Start-date'] = $subscription->getStartDate()->format('l F jS\\, Y');
            $subscriptionInfo['Paid-until'] = $subscription->getPaidUntil()->format('l F jS\\, Y');
            $subscriptionInfo['Number of payments'] = count($subscription->getPayments());
            $subscriptionInfo['Product'] = $subscription->getProduct()->getName();
            $subscriptionInfo['Total cycles paid'] = $subscription->getTotalCyclesPaid();

            /** @var Order $originatingOrder */
            $originatingOrder = $subscription->getOrder();

            if($originatingOrder){
                $subscriptionInfo['Order total due'] = $originatingOrder->getTotalDue();
                $subscriptionInfo['Order taxes'] = $originatingOrder->getTaxesDue();
                $subscriptionInfo['Order shipping'] = $originatingOrder->getShippingDue();
                $subscriptionInfo['Order total paid'] = $originatingOrder->getTotalPaid();

                $subscriptionInfo['Discount applied'] = 'false';
                foreach($originatingOrder->getOrderItems() as $item){
                    if($item->getTotalDiscounted() > 0) $subscriptionInfo['Discount applied'] = 'true';
                }
            }

        }catch(\Exception $e){
            error_log($e);
            $subscriptionInfo['error'] = $e->getMessage();
        }

        return [
            'userInfo' => $userInfo ?? [],
            'subscriptionInfo' => $subscriptionInfo ?? [],
        ];
    }
}
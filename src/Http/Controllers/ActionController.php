<?php

namespace Railroad\Crux\Http\Controllers;

use App\Http\Controllers\User\CancellationController;
use App\Http\Controllers\User\RetentionOfferController;
use App\Maps\ProductAccessMap;
use App\Services\User\UserAccessService;
use Carbon\Carbon;
use Doctrine\ORM\ORMException;
use Exception;
use Illuminate\Http\RedirectResponse as RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Railroad\Crux\Factories\UserPermutationFactory;
use Railroad\Crux\Mail\Agnostic;
use Railroad\Crux\Services\BrandSpecificResourceService;
use Railroad\CustomerIo\Services\CustomerIoService;
use Railroad\Ecommerce\Entities\MembershipAction;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Entities\Product;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\Traits\NotableEntity;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Entities\UserProduct;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionUpdated;
use Railroad\Ecommerce\Events\UserProducts\UserProductUpdated;
use Railroad\Ecommerce\Managers\EcommerceEntityManager;
use Railroad\Ecommerce\Repositories\MembershipActionRepository;
use Railroad\Ecommerce\Repositories\OrderRepository;
use Railroad\Ecommerce\Repositories\ProductRepository;
use Railroad\Ecommerce\Repositories\SubscriptionRepository;
use Railroad\Ecommerce\Services\UserProductService;
use Throwable;

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
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CustomerIoService
     */
    private $customerIoService;

    /**
     * @var UserPermutationFactory
     */
    private $permutationFactory;

    /**
     * @var MembershipActionRepository
     */
    private $membershipActionRepository;

    public static $internalEmailRecipientsByBrand = [
        'drumeo' => ['support+cancellations@drumeo.com'],
        'pianote' => ['support+cancellations@pianote.com'],
        'guitareo' => ['support+cancellations@guitareo.com'],
        'singeo' => ['support+cancellations@singeo.com'],
    ];

    public static $generalSuccessMessageToUser = 'Your account has been updated.';

    public static $generalErrorMessageToUser = 'We\'re sorry, but there\'s been an error. Please reload the page and try ' .
    'again. If that doesn\'t work email or use the chat at the bottom right of your screen to get things sorted out ' .
    'right away.';

    /**
     * CancellationController constructor.
     * @param SubscriptionRepository $subscriptionRepository
     * @param EcommerceEntityManager $ecommerceEntityManager
     * @param UserProductService $userProductService
     * @param ProductRepository $productRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        EcommerceEntityManager $ecommerceEntityManager,
        UserProductService $userProductService,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        CustomerIoService $customerIoService,
        UserPermutationFactory $permutationFactory,
        MembershipActionRepository $membershipActionRepository
    ) {
        $this->ecommerceEntityManager = $ecommerceEntityManager;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userProductService = $userProductService;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->permutationFactory = $permutationFactory;
        $this->membershipActionRepository = $membershipActionRepository;

        $this->brand = config('railcontent.brand');
        $this->customerIoService = $customerIoService;
    }

    // -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=-
    // -=- -=- -=- -=- Public methods  -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=-
    // -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=-

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
            $email->from('system@' . $this->brand . '.com', ucfirst($this->brand) . ' System');
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
        } catch (Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return $this->returnRedirect($success, "Your request has sent. Expect a reply soon!");
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function submitFeedback(Request $request)
    {
        $userFeedback = $request->get('user-feedback');

        $acceptedMonthExtensionOffer = $request->get('accepted-month-extension-offer') ?? false;
        if ($acceptedMonthExtensionOffer) {
            $view = 'crux::email.feedback-from-cancellation-workflow';
            $subject = 'User feedback to make the next month better, from ' . current_user()->getEmail();
            $renewalDate = $request->get('renewal-date');
            $successMessage = 'Your feedback has been submitted, and your membership has been extended by a month!';
            if (!empty($renewalDate)) {
                $successMessage = $successMessage . ' Your new renewal date is: ' . $renewalDate;
            }
        } else {
            $view = 'crux::email.feedback-from-cancellation-workflow';
            $subject = 'User feedback to improve their ' . ucfirst($this->brand) . ' Experience, from ' . current_user(
                )->getEmail();
        }

        try {
            $email = new Agnostic();

            $email->from('system@' . $this->brand . '.com', ucfirst($this->brand) . ' System');
            $email->to(self::$internalEmailRecipientsByBrand[$this->brand] ?? 'support@musora.com');
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
        } catch (Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return $this->returnRedirect(
            true,
            $successMessage ?? 'Your feedback has been submitted, thank you!'
        );
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

        if (UserAccessService::hasClaimedRetentionOfferWithin($this->brand) || $isTrial) {
            return $this->cancel($request);
        }

        $reasons = config('crux.reasons-by-brand')[$this->brand];

        $criteria = [
            $reasons['no-time'] ?? null,
            $reasons['too-expensive'] ?? null,
            $reasons['dont-use-enough'] ?? null,
            $reasons['other-lessons'] ?? null,
        ];

        $match = in_array($reason, $criteria);

        // send to final offer screen depending on use case
        if ($match) {
            if ($subscription->getIntervalType() == config('ecommerce.interval_type_monthly')) {
                return redirect()->route('crux.win-back.monthly-offer');
            } else {
                return redirect()->route('crux.win-back.annual-offer');
            }
        }

        $criteria = [
            $reasons['couldnt-find-lessons'] ?? null,
            $reasons['too-easy'] ?? null,
            $reasons['too-difficult'] ?? null,
            $reasons['lesson-quality'] ?? null,
            $reasons['dont-like-website'] ?? null,
            $reasons['technical-problems'] ?? null,
        ];

        $match = in_array($reason, $criteria);

        if ($match) {
            return redirect()->route('crux.win-back.student-care');
        }

        // other reason
        return $this->cancel($request);
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
            return $this->returnRedirect(
                false,
                'Whoops, something went wrong when we tried to cancel your membership. Please try again or contact ' .
                'our support team.'
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
        } catch (Exception|Throwable $e) {
            return $this->returnRedirect(false);
        }

        event(new SubscriptionUpdated($oldSubscription, $subscription));

        $paidUntilRoundedUp = Carbon::parse($subscription->getPaidUntil()->format('Y-m-d'))->endOfDay();

        // if trial with no payments made revoke access immediately
        $isTrial = in_array($subscription->getProduct()->getId(), ProductAccessMap::trialMembershipProductIds());
        $noPaymentsMade = count($subscription->getPayments()) == 0;
        $revokeAccessImmediately = $isTrial && $noPaymentsMade;

        if ($revokeAccessImmediately) {
            $paidUntilRoundedUp = Carbon::now();
        }

        // ------------------------------------------ PART TWO: send email(s) ------------------------------------------

        // Part 2.1 - email to internal

        $detailedUserInfo = $this->detailedUserInfo($subscription);

        $subject = 'Cancellation notice: ' . current_user()->getEmail();

        try {
            $email = new Agnostic();

            $email->to(self::$internalEmailRecipientsByBrand[$this->brand] ?? 'support@musora.com');
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
        } catch (Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        // Part 2.2 - email to user

        $cancellationSuccessMessage = 'Your membership has been cancelled. You will no longer be automatically ' .
            'billed and your access will end ' . Carbon::parse($paidUntilRoundedUp)->format('l F jS');

        if ($revokeAccessImmediately) {
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
        } catch (Exception $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        // save membership action
        $membershipAction = new MembershipAction();
        /** @var $membershipAction MembershipAction|NotableEntity */
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
        return $this->returnRedirect(false, $cancellationSuccessMessage);
    }

    /**
     * @param Request $request
     */
    public function pause(Request $request)
    {
        $subscription = UserAccessService::getMembershipSubscription(
            current_user()->getId()
        );

        if (empty($subscription)) {
            return $this->returnRedirect(
                false,
                'Whoops, something went wrong when we tried to pause your membership. Please try again or ' .
                'contact our support team.'
            );
        }

        $oldSubscription = clone $subscription;

        $days = $request->get('amount-of-days', 30);

        // extend the subscription first
        $subscription->setPaidUntil($subscription->getPaidUntil()->copy()->addDays($days));

        try {
            $this->ecommerceEntityManager->persist($subscription);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        // update the user product start date which restricts access until that date
        try {
            $userProduct = $this->userProductService->getUserProduct(
                new User(current_user()->getId(), current_user()->getEmail()),
                $subscription->getProduct()
            );
        } catch (Throwable $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        $oldUserProduct = clone $userProduct;

        $userProduct->setStartDate(Carbon::now()->addDays($days));
        $userProduct->setExpirationDate(
            $subscription->getPaidUntil()->addDays(
                config('ecommerce.days_before_access_revoked_after_expiry', 3)
            )
        );

        try {
            $this->ecommerceEntityManager->persist($userProduct);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        event(new UserProductUpdated($userProduct, $oldUserProduct));
        event(new SubscriptionUpdated($oldSubscription, $subscription));

        // save membership action
        /** @var $membershipAction MembershipAction|NotableEntity */
        $membershipAction = new MembershipAction();
        $membershipAction->setUser(new User(current_user()->getId(), current_user()->getEmail()));
        $membershipAction->setBrand(config('ecommerce.brand'));
        $membershipAction->setAction(MembershipAction::ACTION_PAUSE_FOR_AMOUNT_OF_DAYS);
        $membershipAction->setActionAmount($days);
        $membershipAction->setSubscription($subscription);
        $membershipAction->setNote('membership was paused for ' . $days . ' days');

        try {
            $this->ecommerceEntityManager->persist($membershipAction);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        return $this->returnRedirect(
            true,
            'Your membership has been paused for ' .
            $days .
            ' days. Your access will automatically return on: ' .
            $userProduct->getStartDate()->format('F j, Y')
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function acceptMonthlyDiscountRateOffer(Request $request)
    {
        $oldSubscription = UserAccessService::getMembershipSubscription(
            current_user()->getId()
        );

        if (!$oldSubscription) {
            return $this->returnRedirect(
                false,
                'Whoops, something went wrong when we tried to change your membership ' .
                'price. Please try again or contact our support team.'
            );
        }

        try {
            switch ($this->brand) {
                case 'drumeo':
                    $skuForNew = 'DLM-1-month';
                    break;
                case 'pianote':
                    $skuForNew = 'PIANOTE-MEMBERSHIP-1-MONTH';
                    break;
                case 'guitareo':
                    $skuForNew = 'GUITAREO-1-MONTH-MEMBERSHIP';
                    break;
                case 'singeo':
                    $skuForNew = 'singeo-monthly-recurring-membership';
                    break;
            }

            /** @var Product $monthlyProduct */
            $monthlyProduct = $this->productRepository->findOneBy(['sku' => $skuForNew]);

            $discountedRate = BrandSpecificResourceService::pricesOfferCents(
                    $this->brand
                )['monthly'] / 100;

            // cancel the old sub
            $oldSubscription->setCanceledOn(Carbon::now());
            $oldSubscription->setIsActive(false);
            $oldSubscription->setCancellationReason('downgraded to $' . $discountedRate . ' per month billing');

            // create a new one with the proper product and rate
            $newSubscription = new Subscription();

            $newSubscription->setBrand($oldSubscription->getBrand());
            $newSubscription->setType($oldSubscription->getType());
            $newSubscription->setIsActive(true);
            $newSubscription->setStopped(false);
            $newSubscription->setStartDate(Carbon::now());
            $newSubscription->setPaidUntil($oldSubscription->getPaidUntil());
            $newSubscription->setCanceledOn(null);
            $newSubscription->setTotalPrice($discountedRate);

            if ($oldSubscription->getTax()) {
                $priceBeforeTax = $oldSubscription->getTotalPrice() - $oldSubscription->getTax();
                $taxRate = ($oldSubscription->getTotalPrice() - $priceBeforeTax) - 1;
                $newTaxAmount = (($discountedRate / 100) * $taxRate) * 100;
                $newSubscription->setTax($newTaxAmount);
            } else {
                $newSubscription->setTax(0);
            }

            $newSubscription->setCurrency($oldSubscription->getCurrency());
            $newSubscription->setIntervalType(config('ecommerce.interval_type_monthly', 'month'));
            $newSubscription->setIntervalCount(1);
            $newSubscription->setTotalCyclesDue($oldSubscription->getTotalCyclesDue());
            $newSubscription->setTotalCyclesPaid(0);
            $newSubscription->setRenewalAttempt(0);
            $newSubscription->setPaymentMethod($oldSubscription->getPaymentMethod());
            $newSubscription->setUser($oldSubscription->getUser());
            $newSubscription->setCustomer($oldSubscription->getCustomer());
            $newSubscription->setProduct($monthlyProduct);

            $this->ecommerceEntityManager->persist($oldSubscription);
            $this->ecommerceEntityManager->persist($newSubscription);

            $this->userProductService->updateSubscriptionProducts($oldSubscription);
            $this->userProductService->updateSubscriptionProducts($newSubscription);

            event(new SubscriptionUpdated($newSubscription, $newSubscription));

            // save membership actions
            if ($oldSubscription->getIntervalType() != config('ecommerce.interval_type_monthly', 'month')) {
                $switchToMonthlyMembershipAction = new MembershipAction();

                $switchToMonthlyMembershipAction->setUser(
                    new User(current_user()->getId(), current_user()->getEmail())
                );
                $switchToMonthlyMembershipAction->setBrand(config('ecommerce.brand'));
                $switchToMonthlyMembershipAction->setAction(
                    MembershipAction::ACTION_SWITCH_BILLING_INTERVAL_TO_MONTHLY
                );
                $switchToMonthlyMembershipAction->setActionAmount(1);
                $switchToMonthlyMembershipAction->setSubscription($oldSubscription);
                $switchToMonthlyMembershipAction->setNote('membership changed to monthly billing');

                $this->ecommerceEntityManager->persist($switchToMonthlyMembershipAction);
            }

            $priceChangeMembershipAction = new MembershipAction();

            $priceChangeMembershipAction->setUser(new User(current_user()->getId(), current_user()->getEmail()));
            $priceChangeMembershipAction->setBrand(config('ecommerce.brand'));
            $priceChangeMembershipAction->setAction(MembershipAction::ACTION_SWITCH_TO_NEW_PRICE);
            $priceChangeMembershipAction->setActionAmount($discountedRate);
            $priceChangeMembershipAction->setSubscription($oldSubscription);
            $priceChangeMembershipAction->setNote('membership price was changed to $' . $discountedRate);

            $this->ecommerceEntityManager->persist($priceChangeMembershipAction);

            $this->ecommerceEntityManager->flush();
        } catch (ORMException|Throwable $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // use subscription renewal date for success message to user... unless the user-product expiry is a later date

        $renewalDate = Carbon::parse($newSubscription->getPaidUntil());

        $membershipUserProduct = UserAccessService::getMembershipUserProduct();
        if (!$membershipUserProduct) {
            error_log('membership user product not found for user ' . current_user()->getId() . ' in ' . self::class);
            return $this->returnRedirect(false);
        }
        /** @var UserProduct $membershipUserProduct */
        $membershipExpirationDate = Carbon::parse($membershipUserProduct->getExpirationDate());

        if ($membershipExpirationDate->gt($renewalDate)) {
            $renewalDate = $membershipExpirationDate;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        return $this->returnRedirect(
            true,
            'Your monthly membership discount has been activated. Your next renewal date is: ' .
            $renewalDate->format('F j, Y')
        );
    }

    /**
     * This extends the users active subscription and access by 30 days.
     *
     * @return RedirectResponse
     */
    public function acceptExtensionOffer($twoWeeks = false)
    {
        $userId = current_user()->getId();

        try {
            $membershipSubscription = UserAccessService::getMembershipSubscription($userId);

            if ($twoWeeks) {
                $subscription = $this->updateSubscriptionPaidUntilDate(
                    $membershipSubscription,
                    'addDays',
                    14
                );
            } else {
                $subscription = $this->updateSubscriptionPaidUntilDate(
                    $membershipSubscription,
                    'addMonths',
                    1
                );
            }
        } catch (Exception $e) {
            return $this->returnRedirect(
                false,
                'Whoops, something went wrong when we tried to extend your membership. Please try again or ' .
                'contact our support team.'
            );
        }

        $oldSubscription = clone $subscription;
        event(new SubscriptionUpdated($oldSubscription, $subscription));

        // save membership action
        $membershipAction = new MembershipAction();
        /** @var $membershipAction MembershipAction|NotableEntity */
        $membershipAction->setUser(new User(current_user()->getId(), current_user()->getEmail()));
        $membershipAction->setBrand($this->brand);
        $membershipAction->setSubscription($subscription);
        if ($twoWeeks) {
            $membershipAction->setAction(MembershipAction::ACTION_EXTEND_FOR_AMOUNT_OF_DAYS);
            $membershipAction->setActionAmount(14);
            $membershipAction->setNote('membership was extended by 14 days');
        } else {
            $membershipAction->setAction(MembershipAction::ACTION_EXTEND_FOR_AMOUNT_OF_MONTHS);
            $membershipAction->setActionAmount(1);
            $membershipAction->setNote('membership was extended by 1 month');
        }

        try {
            $this->ecommerceEntityManager->persist($membershipAction);
            $this->ecommerceEntityManager->flush();
        } catch (ORMException $e) {
            error_log($e);
            return $this->returnRedirect(false);
        }

        $newRenewalDate = $subscription->getPaidUntil()->format('F j, Y');

        if ($twoWeeks) {
            $routeParams = [];
            $msg = 'Your trial has successfully been extended 14 days. Your new renewal date is: ' . $newRenewalDate;
        }

        return redirect()->route(
            'crux.access-details',
            $routeParams ?? ['open-modal-id' => 'modal-how-can-we-make-next-month-better']
        )->with([
            'success-message' => $msg ?? ('Your access has been extended. Your new renewal date is: ' . $newRenewalDate),
            'renewal-date' => $newRenewalDate
        ]);
    }

    /**
     * @return bool|RedirectResponse
     */
    public function AddStudentPlanAttributeToCurrentUser()
    {
        try {
            $this->AddStudentPlanAttribute();
            return $this->returnRedirect(
                true,
                'An instructor be in touch soon!'
            );
        } catch (Exception $exception) {
            error_log($exception);
            return $this->returnRedirect(
                false,
                self::$generalErrorMessageToUser
            );
        }
    }

    public function resumePaused()
    {
        $user = current_user();
        $userId = current_user()->getId();
        $brand = config('railcontent.brand');

        $permutation = $this->permutationFactory->getPermutation($user, $brand);

        if ($permutation->membershipStatus() != 'paused') {
            return $this->returnRedirect(false);
        }

        try {
            // get the relevant membership-action
            // ----------------------------------

            // note that by default the results we're searching through are ordered by created_at desc thus we're
            // getting the most recent of type MembershipAction::ACTION_PAUSE_FOR_AMOUNT_OF_DAYS

            $membershipActions = $this->membershipActionRepository->getAllUsersMembershipActions(
                current_user()->getId()
            );

            $action = false;

            foreach ($membershipActions as $actionCandidate) {
                if ($actionCandidate->getAction() == MembershipAction::ACTION_PAUSE_FOR_AMOUNT_OF_DAYS) {
                    $action = $actionCandidate;
                    break;
                }
            }

            if (!$action) {
                throw new Exception ('No MembershipAction of required type found for user ' . $userId);
            }

            // get the subscription and user-product
            // -------------------------------------

            $subscription = $action->getSubscription();
            $subscriptionBeforeChanges = clone($subscription);

            $userProduct = UserAccessService::getMembershipUserProduct();
            $oldUserProduct = clone($userProduct);

            // check that product from subscription from action is same product as membershipUserProduct
            if ($subscription->getProduct()->getId() != $userProduct->getProduct()->getId()) {
                throw new Exception (
                    'Product in paused subscription does not match membershipUserProduct (user ' . $userId . ')'
                );
            }

            // calculate the length of time between start date and paid_until date
            // -------------------------------------------------------------------

            $dateStart = Carbon::parse($userProduct->getStartDate());
            $datePaidUntil = Carbon::parse($subscription->getPaidUntil());

            if (Carbon::now()->gt($dateStart)) {
                throw new Exception(
                    'startDate for paused userProduct (' . $userProduct->getId() .
                    ')is in past but resume was called on it. This should not be possible.'
                );
            }

            $hoursToAdd = $dateStart->diffInHours($datePaidUntil) + 1; // adding an extra hour as a kind of rounding-up

            // update subscription and user-product
            // ------------------------------------

            $dateNewPaidUntil = Carbon::now()->addHours($hoursToAdd);
            $subscription->setPaidUntil($dateNewPaidUntil);

            $this->ecommerceEntityManager->persist($subscription);
            $this->ecommerceEntityManager->flush();

            event(new SubscriptionUpdated($subscriptionBeforeChanges, $subscription));
            $this->userProductService->updateSubscriptionProducts($subscription);

            #$userProduct->setStartDate(Carbon::now());
            $userProduct->setStartDate(null);

            $this->ecommerceEntityManager->persist($userProduct);
            $this->ecommerceEntityManager->flush();

            event(new UserProductUpdated($userProduct, $oldUserProduct));

            // create a MembershipAction
            $membershipAction = new MembershipAction();
            /** @var $membershipAction MembershipAction|NotableEntity */
            $membershipAction->setUser(new User($userId, current_user()->getEmail()));
            $membershipAction->setBrand(config('ecommerce.brand'));
            $membershipAction->setAction(MembershipAction::ACTION_RESUME_PAUSED_MEMBERSHIP);
            $membershipAction->setActionReason('user action on access-details page');
            $membershipAction->setSubscription($subscription);
            $this->ecommerceEntityManager->persist($membershipAction);
            $this->ecommerceEntityManager->flush();
        } catch (Exception $exception) {
            error_log($exception);
            return $this->returnRedirect(false);
        }

        return $this->returnRedirect(
            true,
            'You should now have full access again. If you have any issues please let us know right away so we ' .
            'can help you get back to playing!'
        );
    }

    // -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=-
    // -=- -=- -=- -=- Private methods -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=-
    // -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=-

    /**
     * @param bool $success
     * @param null $msg
     * @param string $route
     * @return RedirectResponse
     */
    private function returnRedirect($success = true, $msg = null, $route = 'crux.access-details')
    {
        $type = $success ? 'success-message' : 'error-message';

        $msg = $msg ?? ($success ? self::$generalSuccessMessageToUser : self::$generalErrorMessageToUser);

        return redirect()->route($route)->with([$type => $msg]);
    }

    /**
     * @param $targetProductIds []
     * @param string $carbonMethodName
     * @param string|int $carbonMethodParamValue
     * @return Subscription|boolean
     */
    private function updateSubscriptionPaidUntilDate($subscriptionToUpdate, $carbonMethodName, $carbonMethodParamValue)
    {
        // you're getting the carbon object that is set as an attribute on the entity, not a copy of the carbon object
        $paidUntil = $subscriptionToUpdate->getPaidUntil();

        try {
            /** @var Carbon $extendedPaidUntil */
            $extendedPaidUntil = $paidUntil->$carbonMethodName($carbonMethodParamValue);
        } catch (Exception $e) {
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
        } catch (Throwable $e) {
            error_log($e);
            return false;
        }

        try {
            $this->userProductService->updateSubscriptionProducts($subscriptionToUpdate);
        } catch (Throwable $e) {
            error_log($e);
            return false;
        }

        return $subscriptionToUpdate;
    }

    /**
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    private function addStudentPlanAttribute()
    {
        // customer-io
        $this->customerIoService->createOrUpdateCustomerByUserId(
            current_user()->getId(),
            $this->brand,
            current_user()->getEmail(),
            [$this->brand . '_retention_student_plan' => 'true'],
            current_user()->getCreatedAt()->timestamp
        );
    }

    /**
     * @param Subscription $subscription
     * @return array
     */
    private function detailedUserInfo(Subscription $subscription)
    {
        try {
            $subscriptions = $this->subscriptionRepository->getAllUsersSubscriptions(current_user()->getId());

            try {
                $userProducts = $this->userProductService->getAllUsersProducts(current_user()->getId());
            } catch (ORMException $e) {
                error_log($e);
            }

            $i = 1;
            foreach ($userProducts ?? [] as $userProduct) {
                $product = $userProduct->getProduct();
                $key = 'User product ' . $i . ' of ' . count($userProducts ?? []);

                $expirationDate = 'n/a';
                if ($userProduct->getExpirationDate()) {
                    $expirationDate = $userProduct->getExpirationDate()->format('M jS Y');
                }

                $userInfo[$key] = $product->getName() . ' (sku: ' . $product->getSku(
                    ) . ', expiry-date: ' . $expirationDate . ')';
                $i++;
            }

            $ecomUser = new User(current_user()->getId(), '');
            $orders = $this->orderRepository->findBy(['user' => $ecomUser]);

            $numberOfPaymentsForAllSubscriptionsEver = 0;
            /** @var Subscription $_subscription */
            foreach ($subscriptions as $_subscription) {
                $numberOfPaymentsForAllSubscriptionsEver += count($_subscription->getPayments());
            }

            $userInfo['Number of orders'] = count($orders);
            $userInfo['Number of payments for all subscriptions ever'] = $numberOfPaymentsForAllSubscriptionsEver;
        } catch (Exception $e) {
            error_log($e);
            $userInfo['error'] = $e->getMessage();
        }

        try {
            if (empty($subscription)) {
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

            if ($originatingOrder) {
                $subscriptionInfo['Order total due'] = $originatingOrder->getTotalDue();
                $subscriptionInfo['Order taxes'] = $originatingOrder->getTaxesDue();
                $subscriptionInfo['Order shipping'] = $originatingOrder->getShippingDue();
                $subscriptionInfo['Order total paid'] = $originatingOrder->getTotalPaid();

                $subscriptionInfo['Discount applied'] = 'false';
                foreach ($originatingOrder->getOrderItems() as $item) {
                    if ($item->getTotalDiscounted() > 0) {
                        $subscriptionInfo['Discount applied'] = 'true';
                    }
                }
            }
        } catch (Exception $e) {
            error_log($e);
            $subscriptionInfo['error'] = $e->getMessage();
        }

        return [
            'userInfo' => $userInfo ?? [],
            'subscriptionInfo' => $subscriptionInfo ?? [],
        ];
    }
}
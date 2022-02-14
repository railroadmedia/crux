<?php

namespace Railroad\Crux\Http\Controllers;

use App\Maps\ProductAccessMap;
use App\Services\User\UserAccessService;
use Illuminate\Routing\Controller;
use Railroad\Ecommerce\Entities\Product;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Services\UserProductService;

class AccountDetailsController extends Controller
{

    public static $membershipDetailsSubViews = [
        'membership-details' => 'membership-details',
        'renew-offer' => 'renew-offer',
        'renew-offer-for-expired' => 'renew-offer-for-expired',
        'trial-offer' => 'trial-offer'
    ];

    /*
     *  '7 days' => ['id' => 126,'sku' => 'DLM-Trial-1-month']
     * '30 days' => ['id' => 283,'sku' => 'DLM-Trial-30-Day']
     */
    public static $trialSKUs = ['DLM-Trial-1-month', 'DLM-Trial-30-Day', 'DLM-Trial-Annual-30-Day', 'DLM-Trial-Annual-7-Day'];

    public function accountDetails()
    {
        $edgeAccessIsFromTrial = false;
        $edgeExpirationDate = null;
        $edgeIsExpired = null;
        $userId = current_user()->getId();
        $userProductService = app(UserProductService::class);
        $subscription = UserAccessService::getEdgeSubscription($userId);
        $isLifetime = UserAccessService::isLifetime($userId);
        $userProducts = $userProductService->getAllUsersProducts($userId);

        foreach ($userProducts as $userProduct) {
            /** @var Product $product */
            $product = $userProduct->getProduct();

            $isMembershipProduct = in_array($product->getId(), ProductAccessMap::membershipProductIds());

            if ($isMembershipProduct) {
                $membershipUserProducts[] = $userProduct;
            }

            if ($product->getBrand() == 'drumeo' && !$isMembershipProduct) {
                $ownedNonMembershipProducts[] = $product;
            }
        }

        $userProduct = UserAccessService::getEdgeUserProduct();

        if($userProduct){
            if ($userProduct->getExpirationDate()) {
                $edgeExpirationDate = $userProduct->getExpirationDate();
            }

            $sku = $userProduct->getProduct()->getSku();

            if($subscription && $subscription->getIsActive()){
                // if they get access via a subscription, the product might be different
                $sku = $subscription->getProduct()->getSku();
            }

            if (in_array($sku, self::$trialSKUs)) {
                $edgeAccessIsFromTrial = true;
            }
        }

        $hasEdgeAccess = UserAccessService::isEdge($userId);

        if (!empty($membershipUserProducts)) {
            $edgeIsExpired = UserAccessService::isEdgeExpired($userId);
        }

        // Determine sub-view
        if ($edgeIsExpired) {
            // todo: check that this is what marketing wants
            $membershipDetailsSubView = self::$membershipDetailsSubViews['renew-offer-for-expired'];
        } elseif ($subscription) {

            if ($subscription->getCanceledOn()) {
                // User type ONE - subscription that has been cancelled... and whether or not they currently have access is irrelevant...?
                $membershipDetailsSubView = self::$membershipDetailsSubViews['renew-offer'];
            } else {
                // User type TWO - active subscription
                $membershipDetailsSubView = self::$membershipDetailsSubViews['membership-details'];
                $showCancelMembershipButton = true;
            }
        } else {
            if ($hasEdgeAccess) {
                $membershipDetailsSubView = self::$membershipDetailsSubViews['membership-details'];
            } else {
                $membershipDetailsSubView = self::$membershipDetailsSubViews['trial-offer'];
            }
        }

        if (!$subscription && ($edgeAccessIsFromTrial ?? false) && $hasEdgeAccess) {
            $showCancelMembershipButton = true;
        }

        $linkToSalesPage = 'https://drumeo.com/laravel/public/members/support';

        // trial, 1-month, 2-month, 3-month, 6-month, 1-year, lifetime, null
        $membershipType = null;

        // active, expired, cancelled, lifetime, null
        // null means pack only owner or has access to nothing
        $membershipStatus = UserAccessService::getMembershipSubscriptionState($userId);

        if (empty($subscription) && !empty($userProduct)) {
            $membershipStatus = 'non-recurring';
            $membershipType = 'trial';
            $edgeExpirationDate = $userProduct->getExpirationDate();
        }

        if (!empty($subscription)) {
            if ($edgeAccessIsFromTrial) {
                $membershipType = 'trial';
            } else {
                $membershipType = $subscription->getIntervalCount() . '-' . $subscription->getIntervalType();
            }
        }

        if ($isLifetime) $membershipType = 'lifetime';

        if(UserAccessService::getMembershipStartDateIfPaused($userProduct)) $membershipStatus = 'paused';

        $hasClaimedRetentionOfferAlready = UserAccessService::hasClaimedRetentionOfferWithin(6);

        if (!empty($subscription)) {
            $subscriptionManagedElsewhere = ($subscription->getType() == Subscription::TYPE_APPLE_SUBSCRIPTION ||
                $subscription->getType() == Subscription::TYPE_GOOGLE_SUBSCRIPTION ||
                $subscription->getType() == Subscription::TYPE_PAYPAL_SUBSCRIPTION);
        } else {
            $subscriptionManagedElsewhere = false;
        }

        if($membershipStatus == 'paused' && $membershipType != 'lifetime' && !isset($userProduct)){
            error_log('User product not set when one should be.');
            return redirect()->back()->with(
                [
                    'error-message' => 'We\'re sorry, but there\'s been a technical problem loading that page. Please ' .
                        'try again, and contact Support if the problem persists.'
                ]
            );
        }

        return view(
            'members.account.settings.account-details',
            [
                'hasClaimedRetentionOfferAlready' => $hasClaimedRetentionOfferAlready,
                'subscriptionManagedElsewhere' => $subscriptionManagedElsewhere,
                'linkToSalesPage' => $linkToSalesPage,
                'showCancelMembershipButton' => $showCancelMembershipButton ?? null,
                'edgeAccessIsFromTrial' => $edgeAccessIsFromTrial ?? null,
                'edgeExpirationDate' => $edgeExpirationDate ?? null,
                'membershipDetailsSubView' => $membershipDetailsSubView ?? null,
                'ownedNonMembershipProducts' => $ownedNonMembershipProducts ?? [],
                'subscription' => $subscription ?? null,
                'isLifetime' => $isLifetime,
                'sections' => $this->settingSections(),
                'currentUser' => current_user(),
                'hasEdgeAccess' => $hasEdgeAccess,
                'membershipType' => $membershipType,
                'membershipStatus' => $membershipStatus,
                'userProduct' => $userProduct,
            ]
        );
    }
}
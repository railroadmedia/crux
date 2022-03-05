<?php

namespace Railroad\Crux\UserPermutations;

use App\Maps\ProductAccessMap;
use App\Services\User\UserAccessService;
use Carbon\Carbon;
use Railroad\Ecommerce\Entities\Product;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Services\UserProductService;
use Railroad\Usora\Entities\User;

class UserPermutation
{
    /** @var User $user */
    private $user;

    /** @var string $brand */
    private $brand;

    public function __construct(
        User $user,
        string $brand
    )
    {
        $this->user = $user;
        $this->brand = $brand;
    }

    // top section =====================================================================================================

    /**
     * @return bool
     */
    public function showCancelledNotice(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showRemindOffer(): bool
    {
        return false;
    }

    // left section ====================================================================================================

    /**
     * @return bool
     */
    public function showMembershipType(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showNextRenewalAmount(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showStudentSinceDate(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showAccessEndingDate(): bool
    {
        return false;
    }

    // right section ===================================================================================================

    /**
     * @return bool
     */
    public function showTrialBenefitsList(): bool
    {
        return false;
    }

    // bottom section ==================================================================================================

    /**
     * @return bool
     */
    public function showUpgradeButton()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showStartTrialButton()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showUpgradeFromTrialText()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showStartTrialDescriptionText()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showSavePercentageWithAnnualSubscription()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function showCancelButton()
    {
        return false;
    }

    // =================================================================================================================

    public function hasMembershipAccess()
    {
        return true;
    }

    /*
     * These methods might not be well suited to here. This might risk turning into a god object.
     * These methods might not be well suited to here. This might risk turning into a god object.
     * These methods might not be well suited to here. This might risk turning into a god object.
     */

    public function ownedNonMembershipProducts()
    {
        $userProductService = app(UserProductService::class);
        $userProducts = $userProductService->getAllUsersProducts($this->user->getId());

        foreach ($userProducts as $userProduct) {
            /** @var Product $product */
            $product = $userProduct->getProduct();

            $isMembershipProduct = in_array($product->getId(), ProductAccessMap::membershipProductIds());

//            if ($isMembershipProduct) {
//                $membershipUserProducts[] = $userProduct;
//            }

            if ($product->getBrand() == $this->brand && !$isMembershipProduct) {
                $ownedNonMembershipProducts[] = $product;
            }
        }

        return $ownedNonMembershipProducts ?? [];
    }

    public function membershipType()
    {
        if(UserAccessService::isLifetime($this->user->getId())) {
            $membershipType = 'lifetime';
        }

        $subscription = UserAccessService::getMembershipSubscription($this->user->getId());

        if (!empty($subscription)) {
            if (get_class($this) == MemberTrialWithOutRenewal::class) {
                $membershipType = 'trial';
            } else {
                $membershipType = $subscription->getIntervalCount() . '-' . $subscription->getIntervalType();
            }
        }

        $knownPossibilities = [
            'trial',
            '1-month',
            '2-month',
            '3-month',
            '6-month',
            '1-year',
            'lifetime',
        ];

        if (!empty($membershipType) && !in_array($membershipType, $knownPossibilities)) {
            if (UserAccessService::isMember($this->user->getId())) {
                if (!empty($subscription)) {
                    $type = ucwords($subscription->getIntervalType());
                    $membershipType = $subscription->getIntervalCount() . ' ' . $type . ' member';
                } else {
                    $membershipType = 'member';
                }
            }
        }

        return $membershipType ?? '';
    }

    public function membershipStatus()
    {
        $membershipStatus = UserAccessService::getMembershipSubscriptionState($this->user->getId());

        if($membershipStatus){
            return $membershipStatus;
        }

        $userProduct = UserAccessService::getMembershipUserProduct();

        if (empty($subscription) && !empty($userProduct)) {
            $membershipStatus = 'non-recurring';
        }

        if (UserAccessService::getMembershipStartDateIfPaused($userProduct)) {
            $membershipStatus = 'paused';
        }

        return $membershipStatus;
    }

    public function ifPausedReturnUserProductStartDate()
    {
        if($this->membershipStatus() == 'paused' && $this->membershipType() != 'lifetime'){

            $membershipProduct = UserAccessService::getMembershipUserProduct();

            return $membershipProduct->getStartDate()->format('F j, Y');
        }

        return null;
    }

    public function hasClaimedRetentionOfferAlready()
    {
        return UserAccessService::hasClaimedRetentionOfferWithin(6);
    }

    public function subscriptionManagedElsewhere()
    {
        $subscription = UserAccessService::getMembershipSubscription($this->user->getId());

        if (!empty($subscription)) {
            $subscriptionManagedElsewhere = (
                $subscription->getType() == Subscription::TYPE_APPLE_SUBSCRIPTION ||
                $subscription->getType() == Subscription::TYPE_GOOGLE_SUBSCRIPTION ||
                $subscription->getType() == Subscription::TYPE_PAYPAL_SUBSCRIPTION
            );
        }

        return $subscriptionManagedElsewhere ?? false;
    }

    // =================================================================================================================

    /**
     * @return string|bool
     */
    public function getMembershipTypeNiceName()
    {
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo

        return $membershipTypeNiceName ?? false;
    }

    /**
     * @return int|bool
     */
    public function getNextRenewalAmount()
    {
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo

        return $renewalAmount ?? false;
    }

    /**
     * @return Carbon|bool
     */
    public function getStudentSinceDate()
    {
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo
        // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo

        return $date ?? false;
    }
}
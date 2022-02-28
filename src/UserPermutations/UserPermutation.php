<?php

namespace Railroad\Crux\UserPermutations;

use App\Maps\ProductAccessMap;
use App\Services\User\UserAccessService;
use Carbon\Carbon;
use Railroad\Ecommerce\Entities\Product;
use Railroad\Ecommerce\Services\UserProductService;

class UserPermutation
{

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
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

    public function hasMembership()
    {
        return true;
    }

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

            if ($product->getBrand() == 'drumeo' && !$isMembershipProduct) {
                $ownedNonMembershipProducts[] = $product;
            }
        }

        return $ownedNonMembershipProducts ?? [];
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
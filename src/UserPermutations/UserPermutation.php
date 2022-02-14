<?php

namespace Railroad\Crux\UserPermutations;

use Carbon\Carbon;

class UserPermutation
{

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
<?php

namespace Railroad\Crux\Factories;

use App\Maps\ProductAccessMap;
use App\Services\User\UserAccessService;
use Carbon\Carbon;
use Railroad\Crux\UserPermutations\CancelledMemberWithAccessRemaining;
use Railroad\Crux\UserPermutations\MemberAnnual;
use Railroad\Crux\UserPermutations\MemberAnnualNew;
use Railroad\Crux\UserPermutations\MemberLifetime;
use Railroad\Crux\UserPermutations\MemberMonthly;
use Railroad\Crux\UserPermutations\MemberMonthlyNew;
use Railroad\Crux\UserPermutations\MemberTrialWithOutRenewal;
use Railroad\Crux\UserPermutations\MemberTrialWithRenewal;
use Railroad\Crux\UserPermutations\MemberWithAnomalousNonRenewingAccess;
use Railroad\Crux\UserPermutations\StudentWithoutMembershipAccess;
use Railroad\Crux\UserPermutations\UserPermutation;
use Railroad\Ecommerce\Entities\Product;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\UserProduct;
use Railroad\Ecommerce\Services\UserProductService;
use \Railroad\Usora\Entities\User;

class UserPermutationFactory
{
//    /** @var UserPermutation */
//    private $permutation;

    static private $permutationOptions = [
        'FormerMember',
        'MemberAnnual',
        'MemberAnnualNew',
        'MemberLifetime',
        'MemberMonthly',
        'MemberMonthlyNew',
        'MemberTrialWithOutRenewal',
        'MemberTrialWithRenewal',
        'NotYetMember',
    ];

    private static $membershipDetailsSubViews = [
        'membership-details' => 'membership-details',
        'renew-offer' => 'renew-offer',
        'renew-offer-for-expired' => 'renew-offer-for-expired',
        'trial-offer' => 'trial-offer'
    ];

//    public function __constructor(
//
//    )
//    {
//
//    }

    private function accessFromTrial($subscription)
    {
        /** @var Subscription $subscription */
        if($subscription && $subscription->getIsActive()){
            return in_array($subscription->getProduct()->getSku(), ProductAccessMap::trialMembershipProductIds());
        }

        return false;
    }

    public function subscriptionQualifiesMemberAsNew($subscription)
    {
        if(!$subscription){
            return false;
        }

        /** @var Subscription $subscription */
        $cutOffDate = Carbon::now()->subMonths(3);
        $subscriptionStart = Carbon::parse($subscription->getCreatedAt());
        return $subscriptionStart->gt($cutOffDate);
    }

    /**
     * @param User $user
     * @return UserPermutation
     */
    public function getPermutation(User $user, string $brand): UserPermutation
    {
        $userId = $user->getId();
        $subscription = UserAccessService::getMembershipSubscription($userId);
        $isPaused = false;

        if (!UserAccessService::isMember($userId)) {

            // check that is not a user with a paused subscription
            $membershipUserProduct = UserAccessService::getMembershipUserProduct();

            if($membershipUserProduct){
                $startDate = Carbon::parse($membershipUserProduct->getStartDate());
                $startDateIsInFuture = $startDate->gt(Carbon::now());
            }

            if ($startDateIsInFuture ?? false) {
                $paidUntilDate = Carbon::parse($subscription->getPaidUntil());
                $paidUntilIsAfterStartDate = $startDate->lt($paidUntilDate);
                if ($subscription->getIsActive() && $paidUntilIsAfterStartDate) {
                    $isPaused = true;
                }
            }

            if(!$isPaused){
                return new StudentWithoutMembershipAccess($user, $brand);
            }
        }

        $membershipUserProduct = UserAccessService::getMembershipUserProduct();

        if(!$membershipUserProduct){
            throw new \Exception('No membershipUserProduct found for user ' . $userId);
        }

        $membershipProduct = $membershipUserProduct->getProduct();

        if (UserAccessService::isLifetime($userId)) {
            return new MemberLifetime($user, $brand);
        }

        $nonRenewing = $membershipProduct->getType() == 'digital one time';
        $accessFromTrial = in_array($membershipProduct->getId(), ProductAccessMap::trialMembershipProductIds());

        if ($nonRenewing && $accessFromTrial) {
            return new MemberTrialWithOutRenewal($user, $brand); // todo: WHICH ONE...?
        }

        if (!$subscription) {

            if($accessFromTrial){
                return new MemberTrialWithOutRenewal($user, $brand); // todo: WHICH ONE...?
            }else{
                return new MemberWithAnomalousNonRenewingAccess($user, $brand);
            }
        } else {

            $subscriptionQualifiesMemberAsNew = $this->subscriptionQualifiesMemberAsNew($subscription);

            if ($subscription->getCanceledOn()) {
                return new CancelledMemberWithAccessRemaining($user, $brand);
            }

            if ($subscription->getIntervalType() == 'year' || $subscription->getIntervalType() == 'yearly') {
                if($subscriptionQualifiesMemberAsNew){
                    return new MemberAnnualNew($user, $brand);
                }
                return new MemberAnnual($user, $brand);
            }

            if ($subscription->getIntervalType() == 'month' || $subscription->getIntervalType() == 'monthly') {
                if($subscriptionQualifiesMemberAsNew){
                    return new MemberMonthlyNew($user, $brand);
                }
                return new MemberMonthly($user, $brand);
            }
        }

        throw new \Exception('No UserPermutation fits user ' . $userId);
    }
}
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
    public function getPermutation(User $user): UserPermutation
    {
        $brand = config('railcontent.brand');
        $userId = $user->getId();
        $subscription = UserAccessService::getMembershipSubscription($userId);

        if (!UserAccessService::isMember($userId)) {
            return new StudentWithoutMembershipAccess();
        }

        $membershipUserProduct = UserAccessService::getMembershipUserProduct();

        if(!$membershipUserProduct){
            throw new \Exception('No membershipUserProduct found for user ' . $userId);
        }

        $membershipProduct = $membershipUserProduct->getProduct();

        if (UserAccessService::isLifetime($userId)) {
            return new MemberLifetime();
        }

        $nonRenewing = $membershipProduct->getType() == 'digital one time';
        $accessFromTrial = in_array($membershipProduct->getId(), ProductAccessMap::trialMembershipProductIds());

        if ($nonRenewing) {
            if ($accessFromTrial) {
                return new MemberTrialWithOutRenewal();
            } else {
                throw new \Exception('Unexpected permutation for user ' . $userId);
            }
        }

        if ($subscription) {

            $subscriptionQualifiesMemberAsNew = $this->subscriptionQualifiesMemberAsNew($subscription);

            if ($subscription->getCanceledOn()) {
                return new CancelledMemberWithAccessRemaining();
            }

            if ($subscription->getIntervalType() == 'annual') {
                if($subscriptionQualifiesMemberAsNew){
                    return new MemberAnnualNew();
                }
                return new MemberAnnual();
            }

            if ($subscription->getIntervalType() == 'monthly') {
                if($subscriptionQualifiesMemberAsNew){
                    return new MemberMonthlyNew();
                }
                return new MemberMonthly();
            }
        }

        throw new \Exception('No UserPermutation resolved for used ' . $userId);
    }
}
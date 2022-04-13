<?php

namespace Railroad\Crux\Http\Controllers;

// todo: make this brand agnostic
//use App\Http\Controllers\Profiles\UserSettingsController;
use App\Maps\ProductAccessMap;

// todo: make this brand agnostic
//use App\Services\User\UserAccessService;
use App\Services\User\UserAccessService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Railroad\Crux\Factories\UserPermutationFactory;
use Railroad\Crux\Services\BrandSpecificResourceService;
use Railroad\Ecommerce\Entities\Product;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Services\UserProductService;
use Railroad\Usora\Entities\User;
use Railroad\Crux\Services\NavigationSpecificsDeterminationService as NavHelper;

class ViewController extends Controller
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
    public static $trialSKUs = [
        'DLM-Trial-1-month',
        'DLM-Trial-30-Day',
        'DLM-Trial-Annual-30-Day',
        'DLM-Trial-Annual-7-Day'
    ];

    // todo: make this brand agnostic
//    /**
//     * @var UserSettingsController
//     */
//    private $userSettingsController;
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
        
        return [
            [
                'url' => $this->getUrlForSection($section),
                'icon' => 'fas fa-edit',
                'title' => 'Profile',
                'active' => $section === 'profile',
            ],
            [
                'url' => $this->getUrlForSection($section),
                'icon' => 'fas fa-lock',
                'title' => 'Login Credentials',
                'active' => $section === 'login-credentials',
            ],
            [
                'url' => $this->getUrlForSection($section),
                'icon' => 'far fa-credit-card',
                'title' => 'Payments',
                'active' => $section === 'payments',
            ],
            [
                'url' => $this->getUrlForSection($section),
                'icon' => 'fas fa-bell',
                'title' => 'Settings',
                'active' => $section === 'settings',
            ],
            [
                'url' => $this->getUrlForSection($section),
                'icon' => 'fas fa-calendar-alt',
                'title' => 'Access',
                'active' => $section === 'access-details',
            ],
        ];
    
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    /**
     * @var UserPermutationFactory
     */
    private $permutationFactory;

    public function __construct(
        // todo: make this brand agnostic
        //UserSettingsController $userSettingsController
        UserPermutationFactory $permutationFactory
    ) {
        // todo: make this brand agnostic
        //$this->userSettingsController = $userSettingsController;
        $this->permutationFactory = $permutationFactory;
    }

    public function accessDetails()
    {
        /** @var User $user */
        $user = current_user();
        $brand = config('railcontent.brand');

        try {
            $permutation = $this->permutationFactory->getPermutation($user, $brand);
        } catch (Exception $e) {
            error_log($e);
            if (app('env') == 'development') {
                dd($e->getMessage());
            }
            return redirect()
                ->back()
                ->with([
                    'error-message' => 'We\'re sorry but there\'s been an error, please try again. If the problem ' .
                        'persists please let us know!'
                ]);
        }

        $subscription = UserAccessService::getMembershipSubscription($user->getId());

        $savingsOfAnnualOverMonthly = $this->determineSavingsOfAnnualOverMonthly($brand, $subscription);

        $pointThatSavingsGoFromAdvantageousToInsufficient = 10;

        if ($savingsOfAnnualOverMonthly < $pointThatSavingsGoFromAdvantageousToInsufficient) {
            $savingsOfAnnualOverMonthly = $savingsOfAnnualOverMonthly * -1;
            $savingsOverAnnual = round((1 - (100 / ($savingsOfAnnualOverMonthly + 100))) * 100);
            $pointThatSavingsGoFromAdvantageousToAmazing = 20;
            $insufficientSavingsToJustifyAnnualOffer = true;
            $savingsOverAnnualAreGood = ($savingsOverAnnual >= $pointThatSavingsGoFromAdvantageousToInsufficient) && ($savingsOverAnnual < $pointThatSavingsGoFromAdvantageousToAmazing);
            $savingsOverAnnualAreAmazing = $savingsOverAnnual >= $pointThatSavingsGoFromAdvantageousToAmazing;
            $savingsVsStandardMonthly = $this->determineSavingsOfMonthlyVsStandardMonthly($brand, $subscription);
        }

        $params = [
            'brand' => $brand,
            'sections' => NavHelper::settingSections('account.details'),
            'permutation' => $permutation,
            'accessExpiryDate' => UserAccessService::membershipExpiryDateRegardlessOfCurrentUserState($user->getId()),
            'user' => $user,
            'subscription' => $subscription,

            'ownedNonMembershipProducts' => $permutation->ownedNonMembershipProducts(),
            'membershipType' => $permutation->membershipType(),
            'membershipStatus' => $permutation->membershipStatus(),

            // todo: move these to "helper" static class
            'savingsOfAnnualOverMonthly' => $savingsOfAnnualOverMonthly,
            'insufficientSavingsToJustifyAnnualOffer' => $insufficientSavingsToJustifyAnnualOffer ?? false,
            'featuresList' => BrandSpecificResourceService::featureList($brand),

            'savingsOverAnnual' => $savingsOverAnnual ?? null,
            'savingsOverAnnualAreGood' => $savingsOverAnnualAreGood ?? null,
            'savingsOverAnnualAreAmazing' => $savingsOverAnnualAreAmazing ?? null,
            'savingsVsStandardMonthly' => $savingsVsStandardMonthly ?? null,
        ];

        return view(
            'crux::access-details',
            $params
        );
    }

    public function viewCancelReasonForm()
    {
        return view(
            'crux::cancel-reason-form',
            [
                'brand' => config('railcontent.brand'),
                'subscription' => UserAccessService::getMembershipSubscription(current_user()->getId()),
                'hasClaimedRetentionOfferAlready' => UserAccessService::hasClaimedRetentionOfferWithin(
                    config('railcontent.brand')
                ),
            ]
        );
    }

    /**
     * @return Factory|View
     */
    public function viewAnnualOffer()
    {
        // todo: if offer is insufficiently advantageous compared to current price then don't display this page

        return view(
            'crux::win-back.annual-offer',
            [
                'subscription' => UserAccessService::getMembershipSubscription(current_user()->getId()),
                'brand' => config('railcontent.brand'),
            ]
        );
    }

    /**
     * @return Factory|View
     */
    public function viewMonthlyOffer()
    {
        // todo: if offer is insufficiently advantageous compared to current price then don't display this page

        return view(
            'crux::win-back.monthly-offer',
            [
                'subscription' => UserAccessService::getMembershipSubscription(current_user()->getId()),
                'brand' => config('railcontent.brand'),
            ]
        );
    }

    /**
     * @return Factory|View
     */
    public function viewStudentCare()
    {
        return view(
            'crux::win-back.student-care',
            [
                'subscription' => UserAccessService::getMembershipSubscription(current_user()->getId()),
                'brand' => config('railcontent.brand'),
            ]
        );
    }

    /**
     * @param $brand
     * @param Subscription|null $subscription
     * @return float|int
     *
     * todo: move somewhere better
     */
    private function determineSavingsOfAnnualOverMonthly($brand, ?Subscription $subscription)
    {
        $pricesStandardCents = BrandSpecificResourceService::pricesStandardCents($brand);

        $monthlyPriceTimesTwelveForSavingsValue = $pricesStandardCents['monthly']  * 12;

        if ($subscription) {
            $isMonthlySubscription = ($subscription->getIntervalType() == 'month') && ($subscription->getIntervalCount() == 1);

            if ($isMonthlySubscription) {
                $monthlyPriceInCents = ((int) $subscription->getTotalPrice()) * 100;
                $monthlyPriceTimesTwelveForSavingsValue = $monthlyPriceInCents * 12;
            }
        }

        $ratioRaw = $pricesStandardCents['annual'] / $monthlyPriceTimesTwelveForSavingsValue;
        $ratioAdjusted = $ratioRaw * 100;
        $percentageRaw = 100 - $ratioAdjusted;

        return round($percentageRaw);
    }

    /**
     * @param $brand
     * @param Subscription|null $subscription
     * @return float|int
     *
     * todo: move somewhere better
     */
    private function determineSavingsOfMonthlyVsStandardMonthly($brand, ?Subscription $subscription)
    {
        $pricesStandardCents = BrandSpecificResourceService::pricesStandardCents($brand);

        $standardPriceInCents = $pricesStandardCents['monthly'];

        if ($subscription) {
            $isMonthlySubscription = ($subscription->getIntervalType() == 'month') && ($subscription->getIntervalCount() == 1);

            if ($isMonthlySubscription) {
                $monthlyPriceInCents = $subscription->getTotalPrice() * 100;
                $savings = (int) round((1 - ($monthlyPriceInCents / $standardPriceInCents)) * 100);
            }
        }

        return $savings ?? null;
    }
}
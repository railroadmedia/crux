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
            'featuresList' => BrandSpecificResourceService::featureList($brand),
        ];

        if(!empty($subscription)){
            $savingsParams = BrandSpecificResourceService::savingsInfo($brand, $subscription);
            $params = array_merge($params, $savingsParams);
        }

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
        $user = current_user();
        $brand = config('railcontent.brand');
        $subscription = UserAccessService::getMembershipSubscription($user->getId());
        $savingsParams = BrandSpecificResourceService::savingsInfo($brand, $subscription);

        if ($subscription) {
            $isMonthlySubscription = ($subscription->getIntervalType() == 'month') && ($subscription->getIntervalCount() == 1);

            if ($isMonthlySubscription) {
                $monthlyPrice = ((int) $subscription->getTotalPrice());
            }
        }

        return view(
            'crux::win-back.monthly-offer',
            [
                'subscription' => UserAccessService::getMembershipSubscription(current_user()->getId()),
                'brand' => $brand,
                'savingsOfOfferComparedToCurrent' => $savingsParams['savingsOfOfferComparedToCurrent'] ?? null,
                'priceCurrent' => $monthlyPrice ?? null
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
}
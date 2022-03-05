<?php

namespace Railroad\Crux\Http\Controllers;

// todo: make this brand agnostic
//use App\Http\Controllers\Profiles\UserSettingsController;
use App\Maps\ProductAccessMap;
// todo: make this brand agnostic
//use App\Services\User\UserAccessService;
use App\Services\User\UserAccessService;
use Illuminate\Routing\Controller;
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
    public static $trialSKUs = ['DLM-Trial-1-month', 'DLM-Trial-30-Day', 'DLM-Trial-Annual-30-Day', 'DLM-Trial-Annual-7-Day'];

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
    )
    {
        // todo: make this brand agnostic
        //$this->userSettingsController = $userSettingsController;
        $this->permutationFactory = $permutationFactory;
    }

    public function accessDetails()
    {
        /** @var User $user */
        $user = current_user();
        $brand = config('railcontent.brand');

        try{
            $permutation = $this->permutationFactory->getPermutation($user, $brand);
        } catch (\Exception $e) {
            error_log($e);
            if(app('env') == 'development'){
                dd($e->getMessage());
            }
            return redirect()
                ->back()
                ->with(['error-message' => 'We\'re sorry but there\'s been an error, please try again. If the problem ' .
                    'persists please let us know!']);
        }


        $repo = app(\Railroad\Ecommerce\Repositories\ProductRepository::class);

        // todo: move to "helper" static class
        switch($brand){
            case 'drumeo':
                $priceStandardCentsAnnual = ((int) $repo->findProduct(125)->getPrice()) * 100; // 125, DLM-1-year // 240 as of 220301
                $priceStandardCentsMonthly = ((int) $repo->findProduct(124)->getPrice()) * 100; // 124, DLM-1-month // 29 as of 220301
                break;
            case 'pianote':
                $priceStandardCentsAnnual = ((int) $repo->findProduct(6)->getPrice()) * 100; // 6, PIANOTE-MEMBERSHIP-1-YEAR // 197 as of 220301
                $priceStandardCentsMonthly = ((int) $repo->findProduct(5)->getPrice()) * 100; // 5, PIANOTE-MEMBERSHIP-1-MONTH // 29 as of 220301
                break;
            case 'guitareo':
                $priceStandardCentsAnnual = ((int) $repo->findProduct(18)->getPrice()) * 100; // 18, GUITAREO-1-YEAR-MEMBERSHIP // 127 as of 220301
                $priceStandardCentsMonthly = ((int) $repo->findProduct(17)->getPrice()) * 100; // 17, GUITAREO-1-MONTH-MEMBERSHIP // 15 as of 220301
                break;
            case 'singeo':
                $priceStandardCentsAnnual = ((int) $repo->findProduct(125)->getPrice()) * 100; // 125, singeo-annual-recurring-membership // 127 as of 220301
                $priceStandardCentsMonthly = ((int) $repo->findProduct(409)->getPrice()) * 100; // 409, singeo-monthly-recurring-membership // 15 as of 220301
                break;
        }

        // todo: move to "helper" static class
        $savings = round(100 - (100 * ($priceStandardCentsAnnual / ($priceStandardCentsMonthly * 12))));
        if($savings < 0){
            $savings = $savings * -1;
        }

        $params = [
            'brand' => $brand,
            'sections' => NavHelper::settingSections('account.details'),
            'permutation' => $permutation,
            'accessExpiryDate' => UserAccessService::membershipExpiryDateRegardlessOfCurrentUserState($user->getId()),
            'user' => $user,
            'subscription' => UserAccessService::getMembershipSubscription($user->getId()),

            'ownedNonMembershipProducts' => $permutation->ownedNonMembershipProducts(),
            'membershipType' => $permutation->membershipType(),
            'membershipStatus' => $permutation->membershipStatus(),

            // todo: move these to "helper" static class
            'savings' => $savings,
            'featuresList' => BrandSpecificResourceService::featureList($brand),
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
                'subscription' => UserAccessService::getEdgeSubscription(
                    current_user()->getId()
                ),
                'hasClaimedRetentionOfferAlready' => UserAccessService::hasClaimedRetentionOfferWithin(6),
            ]
        );
    }
}
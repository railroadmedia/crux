@extends('members.account.settings.layout')

@section('meta')
    <title>Access | {{ ucfirst($brand) }}</title>
@endsection

@section('styles')
    <link rel="stylesheet" href="/laravel/public/tailwindcss/tailwind.css">

    <style>
        .renew-link:hover .btn span.bg-success {
            background-color: #13E868;
        }

        .mu-modal {
            transition: opacity 0.25s ease;
        }

        body.mu-modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }
    </style>
@endsection

@section('scripts')

    @if($brand == 'drumeo')
        <script src="{{ _mix('js/profile.js') }}"></script>
    @elseif($brand == 'pianote')
        <script src="{{ mix('assets/members/js/profile.js') }}"></script>
    @elseif($brand == 'guitareo')
        <script src="{{ mix('assets/members/js/profile.js') }}"></script>
    @elseif($brand == 'singeo')
        <script src="{{ mix('assets/members/js/profile.js') }}"></script>
    @else
        <script src="{{ _mix('js/profile.js') }}"></script>
    @endif

@endsection

@section('edit-forms')

    <h1 class="heading tw-pl-8 tw-pt-8">Access Details</h1>

    <!-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
    showCancelledNotice
    showRemindOffer
    showMembershipType
    showNextRenewalAmount
    showStudentSinceDate
    showAccessEndingDate
    showTrialBenefitsList
    showUpgradeButton
    showStartTrialButton
    showUpgradeFromTrialText
    showStartTrialDescriptionText
    showSavePercentageWithAnnualSubscription
    showCancelButton
    -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -->

    <?php $permutation = $permutation ?? null; /* just to get rid of the damn error-notification in the IDE */ ?>

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- Access Levels and owned products ------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

    <?php $ownedNonMembershipProducts = $permutation->ownedNonMembershipProducts(); ?>

    @if(!empty($ownedNonMembershipProducts) || $permutation->hasMembership())
        <div class="tw-flex tw-flex-col tw-p-8 body">
            <h2 class="subheading">Your Access Levels</h2>

            <p class="tw-mt-3">Your {{ ucfirst($brand) }} Account includes:</p>
            <ul class="tw-mt-3 tw-space-y-1">
                @if($permutation->hasMembership())
                    <li>Membership</li>
                @endif

                @foreach($ownedNonMembershipProducts as $product)
                    <?php /** @var $product \Railroad\Ecommerce\Entities\Product */ ?>
                    @if($product->getType() !== 'physical one time')
                        <li>{{ $product->getName() }}</li>
                    @endif
                @endforeach

            </ul>
        </div>
    @endif

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- Expiration Notice ---------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

    @if($permutation->showCancelledNotice())

        <div class="body tw-p-8">
            <h2 class="subheading tw-mb-3">{{ ucfirst($brand) }} Special Offer</h2>
            @if($accessExpiryDate < \Carbon\Carbon::now())
                <p>Your subscription to {{ ucfirst($brand) }} has been canceled and your access ended
                    on {{ $accessExpiryDate->format('F j, Y') }}. Please contact support or reorder on <a
                            href="/">www.{{ $brand }}.com</a> to continue your membership.</p>
            @else
                <p>Your subscription to {{ ucfirst($brand) }} has been canceled and your access will be
                    removed on {{ $accessExpiryDate->format('F j, Y') }}. Please contact support or reorder on <a
                            href="/">www.{{ $brand }}.com</a> to continue your membership.</p>
            @endif
        </div>
    @endif

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- Member --}}

    <?php
        /** @var \Railroad\Crux\UserPermutations\UserPermutation $permutation */
        $membershipType = $permutation->membershipType();
        $membershipStatus = $permutation->membershipStatus();
    ?>

    @if(!empty($membershipType))
        <div class="tw-flex tw-flex-wrap tw-border-0 tw-border-t tw-border-b tw-border-gray-300 tw-border-solid">
            <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 tw-p-8 body tw-items-center tw-justify-center tw-border-0 tw-border-r tw-border-gray-300 tw-border-solid tw-text-center">
                <img src="{{ imgix(\Railroad\Crux\Services\BrandSpecificResourceService::logoUrl($brand), ["auto" => "format"]) }}"
                     alt="{{ ucfirst($brand) }} logo"
                     class="tw-w-80">

                <h2 class="tw-text-lg tw-mt-3 tw-mb-3">
                    @if($membershipType == 'trial')
                        Trial
                    @elseif(\App\Services\User\UserAccessService::isAdministrator(current_user()->getId()))
                        Administrator
                    @elseif($membershipType == '1-month')
                        Monthly Member
                    @elseif($membershipType == '2-month')
                        2 Month Member
                    @elseif($membershipType == '3-month')
                        3 Month Member
                    @elseif($membershipType == '6-month')
                        6 Month Member
                    @elseif($membershipType == '1-year')
                        Annual Member
                    @elseif($membershipType == 'lifetime')
                        Lifetime Member
                    @else
                        $membershipType
                    @endif
                </h2>

                @if ($membershipStatus == 'paused' && $membershipType != 'lifetime' && $permutation->ifPausedReturnUserProductStartDate())
                    <p class="tw-text-gray-600 tw-w-full">
                        <strong>Your membership is paused.</strong><br>
                        Your membership will continue on {{ $permutation->ifPausedReturnUserProductStartDate() }} and your
                        next renewal date has been extended to {{ $subscription->getPaidUntil()->format('F j, Y') }}.
                    </p>
                @elseif ($membershipStatus == 'active' && $membershipType != 'lifetime')
                    <p class="tw-text-gray-600 tw-w-full">
                        Your next renewal is for ${{ $subscription->getTotalPrice() }}
                        on {{ $subscription->getPaidUntil()->format('F j, Y') }}.
                    </p>
                @elseif(($membershipStatus == 'expired' || $membershipStatus == 'canceled' || $membershipStatus == 'non-recurring') &&
                        $membershipType != 'lifetime' && !empty($accessExpiryDate))
                    <p class="tw-text-gray-600 tw-w-full">
                        @if($accessExpiryDate < \Carbon\Carbon::now())
                            Your access ended on {{ $accessExpiryDate->format('F j, Y') }}.
                        @else
                            Your access is ending
                            on {{ $accessExpiryDate->format('F j, Y') }}.
                        @endif
                    </p>
                @endif

                @if ($membershipStatus !== null && $membershipStatus != 'paused')
                    <p class="tw-text-gray-600 tw-w-full">
                        Thank you for being a member since {{ current_user()->getCreatedAt()->format('F j, Y') }}.
                    </p>
                @endif

            </div>
            <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 body tw-p-8">
                <p class="tw-font-bold">{{ ucfirst($brand) }} gives you access to:</p>

                <?php
                    $featuresList = \Railroad\Crux\Services\BrandSpecificResourceService::featureList($brand);
                ?>

                <ul class="tw-mt-3 tw-text-gray-600 tw-space-y-1">
                    @foreach($featuresList as $featureItem)
                        <li>{{ $featureItem }}</li>
                    @endforeach
                </ul>

                @if($membershipStatus == 'active' || $membershipType == 'lifetime')
                    <a href="#" class="tw-mt-3">
                        <p class="mu-modal-open" id="modal-how-can-we-help">Click here if you’d like help getting the
                            most out of your account.</p>
                    </a>
                @endif
            </div>
        </div>
    @endif


    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}



    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}



    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}



    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}



    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

@endsection
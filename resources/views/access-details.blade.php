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

    @if(!empty($ownedNonMembershipProducts) || $permutation->hasMembershipAccess())
        <div class="tw-flex tw-flex-col tw-p-8 body">
            <h2 class="subheading">Your Access Levels</h2>

            <p class="tw-mt-3">Your {{ ucfirst($brand) }} Account includes:</p>
            <ul class="tw-mt-3 tw-space-y-1">
                @if($permutation->hasMembershipAccess())
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
    {{-- Member --------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

    <?php
        /** @var \Railroad\Crux\UserPermutations\UserPermutation $permutation */
        $membershipType = $permutation->membershipType();
        $membershipStatus = $permutation->membershipStatus();
    ?>

    @if($permutation->hasMembershipAccess())
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
                        {{ $membershipType }}
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

    @else {{-- does NOT have membership access--}}

        <div class="tw-flex tw-flex-wrap tw-border-0 tw-border-t tw-border-b tw-border-gray-300 tw-border-solid">
            <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 tw-p-8 body tw-items-center tw-justify-center tw-border-0 tw-border-r tw-border-gray-300 tw-border-solid">
                <img src="{{ imgix(\Railroad\Crux\Services\BrandSpecificResourceService::logoUrl($brand), ["auto" => "format"]) }}"
                     alt="Drumeo logo"
                     class="tw-w-80">
            </div>
            <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 body tw-p-8">
                <p class="tw-font-bold">You’re eligible for a free 7-day trial to get:</p>

                <ul class="tw-mt-3 tw-text-gray-600 tw-space-y-1">
                    <li>Drumeo Method step-by-step curriculum.</li>
                    <li>200+ courses from legendary teachers.</li>
                    <li>Entertaining shows and documentaries.</li>
                    <li>Song breakdowns & Play-Alongs.</li>
                    <li>Weekly live lessons and personal support.</li>
                </ul>
            </div>
        </div>

    @endif

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- Buttons -------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

    <div class="body tw-p-8 tw-pt-10">
        @if($permutation->subscriptionManagedElsewhere())
            <div class="tw-flex tw-flex-col">
                <p class="tw-mb-2">To edit your membership please use the following guides:</p>
                <a href="https://support.apple.com/en-us/HT202039" class="body tw-mb-2" target="_blank">
                    For Apple users</a>
                <a href="https://support.google.com/googleplay/answer/7018481?co=GENIE.Platform%3DAndroid&hl=en"
                   class="body tw-mb-1" target="_blank">
                    For Google users
                </a>
            </div>
        @else
            @if(($membershipStatus == 'active' && $membershipType != 'lifetime' && $membershipType != '1-year') ||
                ($membershipStatus == 'non-recurring' && $membershipType != 'lifetime'))
                <a href="#"
                   class="mu-modal-open tw-uppercase tw-font-bold tw-no-underline bg-drumeo hover:tw-bg-blue-600 tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full"
                   id="modal-upgrade-to-annual">
                    Upgrade Membership
                </a>
            @endif
            @if($membershipStatus == 'canceled' || $membershipStatus == 'expired')
                <a href="/"
                   class="tw-uppercase tw-font-bold tw-no-underline bg-drumeo hover:tw-bg-blue-600 tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full">
                    Renew Your Membership
                </a>
            @endif
            @if($membershipStatus == 'paused')
                <a href="/"
                   class="tw-uppercase tw-font-bold tw-no-underline bg-drumeo hover:tw-bg-blue-600 tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full">
                    Continue Your Membership
                </a>
            @endif

            @if($membershipStatus == 'active' && ($membershipType != 'lifetime') && !$permutation->hasClaimedRetentionOfferAlready())
                @php
                    if (in_array($subscription->getProduct()->getId(), \App\Maps\ProductAccessMap::trialMembershipProductIds()) && count($subscription->getPayments()) == 0) {
                        $modalId = 'modal-extend-trial-14-days';
                    } else {
                        if ($subscription->getStartDate() > \Carbon\Carbon::now()->subDays(90)) {
                            $modalId = 'modal-free-30-days';
                        } else {
                            $modalId = 'modal-post-90-day-cancel-letter';
                        }
                    }
                @endphp

                <a href="#" id="{{ $modalId }}"
                   class="mu-modal-open tw-uppercase tw-font-bold tw-no-underline tw-p-3 tw-pl-16 tw-pr-16">
                    Cancel Membership
                </a>
            @endif

            @if($membershipStatus == 'active' && ($membershipType != 'lifetime') && $permutation->hasClaimedRetentionOfferAlready())
                <a href="{{ url()->route('user.settings.cancel.cancel-reason-form') }}"
                   class="tw-uppercase tw-font-bold tw-no-underline tw-p-3 tw-pl-16 tw-pr-16">
                    Cancel Membership
                </a>
            @endif

            @if(empty($membershipType) && !$permutation->hasMembershipAccess())
                <a href="/laravel/public/shopping-cart/api/query?products[DLM-Trial]=1,month,1&locked=true"
                   class="tw-uppercase tw-font-bold tw-no-underline bg-drumeo hover:tw-bg-blue-600 tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full">
                    Start Free Trial
                </a>
            @endif
        @endif
    </div>

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- Message -------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

    @if(!$permutation->subscriptionManagedElsewhere() && $membershipType != 'lifetime')
        <div class="body tw-p-8 tw-pt-2">
            @if($membershipStatus == 'active' && $membershipType != 'lifetime' && $membershipType != '1-year')
                <p class="tw-text-gray-600 tw-italic">
                    Save {{ $savings }}% with an annual plan.
                </p>
            @elseif($membershipStatus == 'canceled' || $membershipStatus == 'expired' || $membershipStatus == 'paused')
                <p class="tw-text-gray-600 tw-italic">
                    This link will take you to reorder on <a href="/">www.drumeo.com</a>.
                    Any purchased access will be added to your existing
                    time. If you’d prefer, you can <a href="{{ url()->route('members.support') }}">click here</a> to
                    contact
                    Drumeo Support to restart your membership.
                </p>
            @elseif($membershipStatus == 'non-recurring')
                <p class="tw-text-gray-600 tw-italic">
                    Extend your membership beyond a trial at <a href="/">www.drumeo.com</a>
                </p>
            @elseif(empty($membershipType))
                <p class="tw-text-gray-600 tw-italic">
                    One time offer: 7 days free, no payment plan, starts immediately.
                </p>
            @endif
        </div>
    @endif

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}



    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

@endsection
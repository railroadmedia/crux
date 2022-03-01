@extends('members.account.settings.layout')

@section('meta')
    <title>Access | Drumeo</title>
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
    {{-- Access Levels -------------------------------------------------------------------------------------------- --}}
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
            <h2 class="subheading tw-mb-3">Drumeo Special Offer</h2>
            @if($accessExpiryDate < \Carbon\Carbon::now())
                <p>Your subscription to Drumeo has been canceled and your access ended
                    on {{ $accessExpiryDate->format('F j, Y') }}. Please contact support or reorder on <a
                            href="/">www.drumeo.com</a> to continue your membership.</p>
            @else
                <p>Your subscription to Drumeo has been canceled and your access will be
                    removed on {{ $accessExpiryDate->format('F j, Y') }}. Please contact support or reorder on <a
                            href="/">www.drumeo.com</a> to continue your membership.</p>
            @endif
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



    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

@endsection
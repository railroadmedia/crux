{{--@extends('members.account.settings.layout')--}}
@extends('crux::layout')

@section('meta')
    <title>Access | {{ ucfirst($brand) }}</title>
@endsection

@section('styles')

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

    @if($brand == 'pianote')
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

    {{-- ---------------------------------------------------------------------------------------------------------- --}}
    {{-- Access Levels and owned products ------------------------------------------------------------------------- --}}
    {{-- ---------------------------------------------------------------------------------------------------------- --}}

    @if(!empty($ownedNonMembershipProducts) || $permutation->hasMembershipAccess())
        <div class="tw-flex tw-flex-col tw-p-8 body">
            <h2 class="subheading">Your Access Levels</h2>

            <p class="tw-mt-3">Your {{ ucfirst($brand) }} Account includes:</p>
            <ul class="tw-mt-3 tw-space-y-1">

                @if($permutation->membershipType() == 'lifetime')
                    <li>Lifetime Membership</li>
                @elseif($permutation->hasMembershipAccess())
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
                    @endif
                </h2>

                @if ($membershipStatus == 'paused' && $membershipType != 'lifetime' && $permutation->ifPausedReturnUserProductStartDate())
                    <p class="tw-text-gray-600 tw-w-full">
                        <strong>Your membership is paused.</strong><br>
                        Your membership will continue on {{ $permutation->ifPausedReturnUserProductStartDate() }} and
                        your
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
                            Your access is ending on {{ $accessExpiryDate->format('F j, Y') }}.
                        @endif
                    </p>
                @endif

                @if ($membershipStatus !== null && $membershipStatus != 'paused')
                    <p class="tw-text-gray-600 tw-w-full">
                        Thank you for being a student since {{ current_user()->getCreatedAt()->format('F j, Y') }}.
                    </p>
                @endif

            </div>
            <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 body tw-p-8">
                <p class="tw-font-bold">{{ ucfirst($brand) }} gives you access to:</p>

                <ul class="tw-mt-3 tw-text-gray-600 tw-space-y-1">
                    @foreach($featuresList as $featureItem)
                        <li>{{ $featureItem }}</li>
                    @endforeach
                </ul>

                @if($membershipStatus == 'active' || $membershipType == 'lifetime')
                    <a href="#" class="tw-mt-3 tw-no-underline">
                        <p class="mu-modal-open text-{{ $brand }}" id="modal-how-can-we-help">Click here if you???d like
                            help getting the
                            most out of your account.</p>
                    </a>
                @endif
            </div>
        </div>

    @else {{-- does NOT have membership access--}}

    <div class="tw-flex tw-flex-wrap tw-border-0 tw-border-t tw-border-b tw-border-gray-300 tw-border-solid">
        <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 tw-p-8 body tw-items-center tw-justify-center tw-border-0 tw-border-r tw-border-gray-300 tw-border-solid">
            <img src="{{ imgix(\Railroad\Crux\Services\BrandSpecificResourceService::logoUrl($brand), ["auto" => "format"]) }}"
                 alt="{{ $brand }} logo"
                 class="tw-w-80">
        </div>
        <div class="tw-flex tw-flex-col tw-w-full md:tw-w-1/2 body tw-p-8">
            <p class="tw-font-bold">You???re eligible for a free 7-day trial to get:</p>

            <ul class="tw-mt-3 tw-text-gray-600 tw-space-y-1">
                @if(in_array($brand, ['drumeo', 'pianote']))
                    <li>{{ ucfirst($brand) }} Method step-by-step curriculum.</li>
                    <li>200+ courses from legendary teachers.</li>
                @else
                    <li>{{ ucfirst($brand) }} step-by-step curriculum.</li>
                    <li>Courses from legendary teachers.</li>
                @endif
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
            @if($membershipStatus == 'active' && $membershipType != 'lifetime' && $membershipType != '1-year')
                @if(!$savingsOverAnnualAreAmazing)
                    <a href="#"
                       class="mu-modal-open tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full"
                       id="modal-upgrade-to-annual">
                        Upgrade Membership
                    </a>
                @endif
            @endif
            @if($membershipStatus == 'non-recurring' && $membershipType != 'lifetime')
                <a href="#"
                   class="mu-modal-open tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full"
                   id="modal-upgrade-to-renewing">
                    Continue Your Access
                </a>
            @endif
            @if($membershipStatus == 'canceled' || $membershipStatus == 'expired')
                <a href="/"
                   class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full">
                    Renew Your Membership
                </a>
            @endif
            @if($membershipStatus == 'paused')
                <form method="post"
                      action="{{ url()->route('crux.submit.resume-paused') }}">

                    {{ csrf_field() }}

                    <a href="#"
                       onclick="this.parentNode.submit(); return false;"
                       class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full">
                        Continue Your Membership
                    </a>
                </form>
            @endif

            @if($membershipStatus == 'active' && ($membershipType != 'lifetime') && !$permutation->hasClaimedRetentionOfferAlready())
                @php
                    if (in_array($subscription->getProduct()->getId(), \App\Maps\ProductAccessMap::trialMembershipProductIds()) && count($subscription->getPayments()) == 0) {
                        $modalId = 'modal-extend-trial-14-days';
                    } else {
                        if ($subscription->getStartDate() > \Carbon\Carbon::now()->subDays(90)) {
                            $modalId = 'modal-free-month';
                        } else {
                            $modalId = 'modal-post-90-day-cancel-letter';
                        }
                    }
                @endphp

                <a href="#" id="{{ $modalId }}"
                   class="mu-modal-open tw-uppercase tw-font-bold tw-no-underline tw-p-3 tw-pl-16 tw-pr-16 text-{{ $brand }}">
                    Cancel Membership
                </a>
            @endif

            @if($membershipStatus == 'active' && ($membershipType != 'lifetime') && $permutation->hasClaimedRetentionOfferAlready())
                <a href="{{ url()->route('crux.cancel-reason-form') }}"
                   class="tw-uppercase tw-font-bold tw-no-underline tw-p-3 tw-pl-16 tw-pr-16 text-{{ $brand }}">
                    Cancel Membership
                </a>
            @endif

            @if(empty($membershipType) && !$permutation->hasMembershipAccess())
                @php
                    if ($brand == 'drumeo') {
                        $trialUrl = '/laravel/public/shopping-cart/api/query?products[DLM-Trial]=1,month,1&locked=true';
                    } elseif ($brand == 'pianote') {
                        $trialUrl = '/ecommerce/add-to-cart?products[PIANOTE-MEMBERSHIP-TRIAL]=1&redirect=%2Forder&locked=true';
                    } elseif ($brand == 'guitareo') {
                        $trialUrl = '/ecommerce/add-to-cart?products[GUITAREO-7-DAY-TRIAL-ONE-TIME]=1&redirect=%2Forder&locked=true';
                    } else {
                        $trialUrl = '/ecommerce/add-to-cart?products[singeo-monthly-recurring-7-day-trial-membership]=1&redirect=%2Forder&locked=true';
                    }
                @endphp
                <a href="{{ $trialUrl }}"
                   class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full">
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

                @if(!empty($insufficientSavingsToJustifyAnnualOffer))
                    @if($savingsOverAnnualAreAmazing)
                        <p class="tw-text-gray-600 tw-italic">
                            You've snagged an amazingly good monthly rate, saving {{ $savingsVsStandardMonthly }}%
                            compared the standard monthly rate and {{ $savingsOverAnnual }}% compared to an annual
                            membership!
                        </p>
                    @else
                        <p class="tw-text-gray-600 tw-italic">
                            Make a commitment to your learning and get great bonuses. Click above to see about upgrading to an annual membership.
                        </p>
                    @endif
                @else
                    @if(!empty($savingsOfAnnualOverMonthly))
                        <p class="tw-text-gray-600 tw-italic">
                            Save {{ $savingsOfAnnualOverMonthly }}% with an annual plan.
                        </p>
                    @endif
                @endif
            @elseif($membershipStatus == 'canceled' || $membershipStatus == 'expired')
                <p class="tw-text-gray-600 tw-italic">
                    This link will take you to reorder on <a href="/">www.{{ $brand }}.com</a>.
                    Any purchased access will be added to your existing
                    time. If you???d prefer, you can <a href="{{ url()->route('members.support') }}">click here</a> to
                    contact Support to restart your membership.
                </p>
            @elseif($membershipStatus == 'paused')
                <p class="tw-text-gray-600 tw-italic">
                    The above button will restart your access right away. You can also
                    <a href="{{ url()->route('members.support') }}">contact us</a> for help.
                </p>
            @elseif($membershipStatus == 'non-recurring')
                <p class="tw-text-gray-600 tw-italic">
                    Extend your membership beyond a trial at <a href="/">www.{{ $brand }}.com</a>
                </p>
            @elseif(empty($membershipType))
                <p class="tw-text-gray-600 tw-italic">
                    @if($brand == 'drumeo')
                        One time offer: 7 days free, no payment plan, starts immediately.
                    @else
                        Start your journey with a free trial.
                    @endif
                </p>
            @endif
        </div>
    @endif

@endsection

@section('body-top')
    {{-- Buttons for testing purposes only. --}}

    {{--            <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                    id="modal-extend-trial-14-days">--}}
    {{--                Open Trial 14 Days--}}
    {{--            </button>--}}

    {{--            <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                    id="modal-free-month">--}}
    {{--                Open Free 30 Days--}}
    {{--            </button>--}}

    {{--            <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                    id="modal-how-can-we-help">--}}
    {{--                Open How Can We Help--}}
    {{--            </button>--}}

    {{--            <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                    id="modal-how-can-we-make-next-month-better">--}}
    {{--                Open How Can We Make The Next 30 Days Better--}}
    {{--            </button>--}}

    {{--            <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                    id="modal-upgrade-to-annual">--}}
    {{--                Open Upgrade To Annual--}}
    {{--            </button>--}}
    
    {{--                <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                        id="modal-upgrade-to-renewing">--}}
    {{--                    Open Upgrade To Renewing--}}
    {{--                </button>--}}

    {{--            <button class="mu-modal-open tw-bg-transparent tw-border tw-border-gray-500 hover:tw-border-indigo-500 tw-text-gray-500 hover:tw-text-indigo-500 tw-font-bold tw-py-2 tw-px-4 tw-rounded-full"--}}
    {{--                    id="modal-post-90-day-cancel-letter">--}}
    {{--                Open 90 Day Cancel Letter--}}
    {{--            </button>--}}

    {{-- Modals --}}
    {{-- Extend Trial 14 Days  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-extend-trial-14-days'])
        @slot('contentSlot')
            <h1 class="heading tw-text-center">Need more time?</h1>

            @php
                $instrument = 'music';

                if ($brand == 'drumeo') {
                    $instrument = 'drum';
                } elseif ($brand == 'pianote') {
                    $instrument = 'piano';
                } elseif ($brand == 'guitareo') {
                    $instrument = 'guitar';
                } elseif ($brand == 'singeo') {
                    $instrument = 'vocal';
                }
            @endphp

            <p class="body tw-text-center tw-mt-6">
                Everyone gets busy and you may not have had enough time to watch lessons and practice.
                <strong>Extend your trial an additional 14 days</strong> on us to keep access to the best
                {{ $instrument }} lessons in the world.
            </p>

            <form method="post"
                  action="{{ url()->route('crux.submit.accept-extension-offer', ['two_weeks' => true]) }}">

                {{ csrf_field() }}

                <a href="#"
                   onclick="this.parentNode.submit(); return false;"
                   class="tw-block tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8">
                    Yes, Extend My Trial
                </a>
            </form>

            <a href="{{ url()->route('crux.cancel-reason-form')}}"
               class="tw-uppercase tw-font-bold tw-no-underline tw-mt-6 text-{{ $brand }}">
                No Thanks, Cancel Membership
            </a>

        @endslot
    @endcomponent

    {{-- 30 Days Free  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-free-month'])
        @slot('contentSlot')
            <h1 class="heading tw-text-center">Uh oh! It looks like you<br> didn???t love your lessons?</h1>

            <p class="body tw-text-center tw-mt-6">
                That???s not okay -- and we???d like a 2nd chance. Just click the ???FREE MONTH??? button below to keep your
                membership for one more month, totally free. Your renewal date will simply be delayed by one month.
            </p>

            <p class="body tw-text-center tw-mt-6">
                There???s only one catch??? we???ll also quickly ask you what we can improve to hopefully give you a
                better experience in the next month.
            </p>

            {{-- would this be better maybe though? --}}

            {{--            <p class="body tw-text-center tw-mt-6">--}}
            {{--                That???s not okay???we???d like another chance! Click ???FREE MONTH??? below for one month, totally free.--}}
            {{--            </p>--}}

            {{--            <p class="body tw-text-center tw-mt-6">--}}
            {{--                One catch???tell us what we can do better.--}}
            {{--            </p>--}}

            {{-- todo: this should immediately extend their renewal date 30 days and access and go back to the account details page with a message --}}

            <form method="post"
                  action="{{ url()->route('crux.submit.accept-extension-offer') }}">

                {{ csrf_field() }}

                <a href="#"
                   class="tw-uppercase tw-block tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8"
                   onclick="this.parentNode.submit(); return false;">
                    Free Month
                </a>
            </form>


            @if(!empty($subscription))
                <a href="{{ url()->route('crux.cancel-reason-form')}}"
                   class="tw-uppercase tw-font-bold tw-no-underline tw-mt-6 text-{{ $brand }}">
                    No Thanks, Cancel Membership
                </a>
            @endif
        @endslot
    @endcomponent

    {{-- How Can We Help?  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-how-can-we-help'])
        @slot('contentSlot')

            {{-- todo: this should submit the normal cancel help email that goes to the person is does now and return back to the account details page with a success message --}}
            <form method="post" action="{{ url()->route('crux.submit.send-help-email') }}"
                  class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-relative">

                {{ csrf_field() }}

                <h1 class="heading tw-text-center">How can we help?</h1>

                <div class="tw-text-left">
                    <p class="body tw-mt-6">
                        Select any of the issues you???d like help with:
                    </p>
                    {{--<div class="tw-ml-3">--}}
                    <ul class="tw-ml-3">
                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="direction" value="direction"
                                   class="tw-mr-3 tw-mt-6">
                            <label for="direction">I need more direction</label>
                        </li>
                        {{-- </p> --}}


                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="time" value="time" class="tw-mr-3">
                            <label for="time">I don???t have enough time</label>
                        </li>
                        {{-- </p> --}}


                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="watch" value="watch" class="tw-mr-3">
                            <label for="watch">I don???t know what lesson to watch</label>
                        </li>
                        {{-- </p> --}}


                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="easy" value="easy" class="tw-mr-3">
                            <label for="easy">The lessons are too easy</label>
                        </li>
                        {{-- </p> --}}


                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="difficult" value="difficult" class="tw-mr-3">
                            <label for="difficult">The lessons are too difficult</label>
                        </li>
                        {{-- </p> --}}


                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="website" value="website" class="tw-mr-3">
                            <label for="website">I don???t know how to use the website/app.</label>
                        </li>
                        {{-- </p> --}}


                        {{-- <p class="body"> --}}
                        <li>
                            <input type="radio" name="help-issue" id="other" value="other" class="tw-mr-3">
                            <label for="other">Other</label>
                        </li>
                        {{-- </p> --}}


                    </ul>

                    <textarea placeholder="Send your questions to a {{ ucfirst($brand) }} teacher..."
                              class="tw-mt-6 tw-rounded-lg" name="text-input"></textarea>

                    {{--<label for="email-input" class="tw-py-1 tw-mt-6 tw-block">We'll get back to you at "{{ current_user()->getEmail() }}". If you prefer a different address, please enter it here (optional):</label>--}}
                    {{--<input type="text" name="email" id="email-input" class="tw-mt-1 tw-pt-0 tw-rounded-lg"--}}
                    {{--placeholder="Email address">--}}
                </div>

                <button
                        class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8 tw-border-0"
                        style="cursor:pointer">
                    Send Message
                </button>
            </form>
        @endslot
    @endcomponent

    {{-- How Can We Make The Next 30 Days Better?  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-how-can-we-make-next-month-better'])
        @slot('contentSlot')

            <form method="post" action="{{ url()->route('crux.submit.feedback') }}"
                  class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-relative">

                {{ csrf_field() }}

                <input type="hidden" name="accepted-month-extension-offer" value="true">

                @if(session()->has('renewal-date'))
                    <input type="hidden" name="renewal-date" value="{{ session()->get('renewal-date') }}">
                @endif

                <p>We???ve added an extra month to your account!</p>

                <h1 class="heading tw-text-center tw-mt-4">How can we make the next month better?</h1>

                <textarea placeholder="Type your feedback here..."
                          name="user-feedback"
                          class="tw-mt-6 tw-rounded-lg tw-w-full"></textarea>

                <button
                        type="submit"
                        class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8 tw-border-0">
                    Send Feedback >>
                </button>
            </form>
        @endslot
    @endcomponent

    {{-- How Can We Make Your Drumeo Experience Better?  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-how-can-we-make-your-drumeo-edge-experience-better'])
        @slot('contentSlot')

            <form method="post" action="{{ url()->route('crux.submit.feedback') }}"
                  class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-relative">

                {{ csrf_field() }}

                <h1 class="heading tw-text-center tw-mt-4">How can we make your {{ ucfirst($brand) }} experience better?</h1>

                <textarea placeholder="Type your feedback here..."
                          name="user-feedback"
                          class="tw-mt-6 tw-rounded-lg tw-w-full"></textarea>

                <button
                        type="submit"
                        class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8 tw-border-0">
                    Send Feedback >>
                </button>
            </form>
        @endslot
    @endcomponent

    {{-- Upgrade To Annual  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-upgrade-to-annual'])
        @slot('contentSlot')
            @if(!empty($savingsOfAnnualOverMonthly))
                <h1 class="heading tw-text-center">Save {{ $savingsOfAnnualOverMonthly }}% with an annual plan.</h1>
            @else
                <h1 class="heading tw-text-center">Save with an annual plan.</h1>
            @endif
            <div class="tw-flex md:tw-flex-row tw-flex-col tw-mt-10 tw-w-full">
                <div class="tw-flex tw-flex-col md:tw-w-1/2 tw-w-full tw-mr-3 tw-items-center tw-text-center tw-rounded-lg tw-bg-gray-200 tw-p-0 tw-py-8">
                    <h1 class="tw-uppercase tw-font-bold">Your <br>Plan</h1>
                    @if(!empty($subscription))
                        <p class="tw-mt-4 tw-leading-6">${{ $subscription->getTotalPrice() }} per month<br> =
                            ${{ $subscription->getTotalPrice() * 12 }} per year.</p>
                        <a href="#"
                           class="tw-uppercase tw-font-bold tw-no-underline tw-bg-black hover:tw-bg-gray-900 tw-p-3 tw-pl-8 tw-pr-8 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm mu-modal-close">
                            Keep This Plan
                        </a>
                    @elseif(!empty($edgeExpirationDate))
                        <p class="tw-mt-4 tw-leading-6">Temporary access until
                            <br>{{ $edgeExpirationDate->format('F j, Y') }}.</p>
                        <a href="#"
                           class="tw-uppercase tw-font-bold tw-no-underline tw-bg-black hover:tw-bg-gray-900 tw-p-3 tw-pl-8 tw-pr-8 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm mu-modal-close">
                            Keep This Plan
                        </a>
                    @else
                        <p class="tw-mt-4 tw-leading-6">Temporary access.</p>
                        <a href="#"
                           class="tw-uppercase tw-font-bold tw-no-underline tw-bg-black hover:tw-bg-gray-900 tw-p-3 tw-pl-8 tw-pr-8 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm mu-modal-close">
                            Keep This Plan
                        </a>
                    @endif
                </div>
                <div class="tw-flex tw-flex-col md:tw-w-1/2 tw-w-full tw-ml-3 tw-items-center tw-text-center tw-rounded-lg {{ \Railroad\Crux\Services\BrandSpecificResourceService::styleBorderClass($brand) }} tw-border-2 tw-border-solid tw-p-3 tw-py-8">
                    <h1 class="tw-uppercase">Annual <br>Plan</h1>
                    @if(!empty($insufficientSavingsToJustifyAnnualOffer) || empty($savingsOfAnnualOverMonthly))
                        <p class="tw-mt-4 tw-leading-6">Get limited time bonuses.</p>
                    @else
                        @if(!empty($savingsOfAnnualOverMonthly))
                            <p class="tw-mt-4 tw-leading-6">Save {{ $savingsOfAnnualOverMonthly }}%<br>+ get limited time
                                bonuses.
                            </p>
                        @endif
                    @endif
                    <a href="/#customize-anchor"
                       class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm">
                        See Offer
                    </a>
                </div>
            </div>

            <p class="body tw-text-center tw-mt-10">
                By completing the checkout process on the next page, your monthly billing will be stopped and replaced
                by an annual billing plan at the posted rate.
            </p>
        @endslot
    @endcomponent

    @php
        $monthlyLink = '/';
        if ($brand == 'drumeo') {
            $monthlyLink = 'https://www.drumeo.com/laravel/public/shopping-cart/api/query?products[DLM-1-month]=1&redirect=%2Forder&locked=true';
        } elseif ($brand == 'pianote') {
            $monthlyLink = 'https://www.pianote.com/ecommerce/add-to-cart?products[PIANOTE-MEMBERSHIP-1-MONTH]=1&redirect=%2Forder&locked=true';
        } elseif ($brand == 'guitareo') {
            $monthlyLink = 'https://www.guitareo.com/ecommerce/add-to-cart?products[GUITAREO-1-MONTH-MEMBERSHIP]=1&redirect=%2Forder&locked=true';
        } elseif ($brand == 'singeo') {
            $monthlyLink = 'https://www.singeo.com/ecommerce/add-to-cart?products[singeo-monthly-recurring-membership]=1&redirect=%2Forder&locked=true';
        }
    @endphp

    {{-- start a membership  --}}
    @component('crux::partials._modal', ['modalId' => 'modal-upgrade-to-renewing'])
        @slot('contentSlot')
            <h1 class="heading tw-text-center">Choose your plan</h1>
            <div class="tw-flex md:tw-flex-row tw-flex-col tw-mt-10 tw-w-full">

                <div class="tw-flex tw-flex-col md:tw-w-1/2 tw-w-full tw-ml-3 tw-items-center tw-text-center tw-rounded-lg tw-bg-white tw-border-2 tw-border-solid tw-border-gray-400 tw-p-3 tw-py-8">
                    <h1 class="tw-uppercase tw-font-bold">Monthly</h1>
                    <p class="tw-mt-4 tw-leading-6">
                        Our lowest price
                        <br>to start
                    </p>
                    <a href="{{ $monthlyLink }}"
                       class="tw-uppercase tw-font-bold tw-no-underline tw-bg-gray-400 hover:tw-bg-gray-900 tw-p-3 tw-pl-8 tw-pr-8 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm mu-modal-close">
                        Start Here
                    </a>
                </div>

                <div class="tw-flex tw-flex-col md:tw-w-1/2 tw-w-full tw-ml-3 tw-items-center tw-text-center tw-rounded-lg {{ \Railroad\Crux\Services\BrandSpecificResourceService::styleBorderClass($brand) }} tw-border-2 tw-border-solid tw-p-3 tw-py-8">
                    <h1 class="tw-uppercase">Annual</h1>

                    @if(!empty($savingsOfAnnualOverMonthly))
                        <p class="tw-mt-4 tw-leading-6">Save {{ $savingsOfAnnualOverMonthly }}% vs monthly
                            <br>+ limited time bonuses
                        </p>
                    @else
                        <p class="tw-mt-4 tw-leading-6">Save compared to monthly
                            <br>+ limited time bonuses
                        </p>
                    @endif

                    <a href="/"
                       target="_blank"
                       class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm">
                        See Offer
                    </a>
                </div>
            </div>

            <p class="body tw-text-center tw-mt-10">
                By completing the checkout process on the next page,
{{--                <br>you can continue your musical education without interuption.--}}
{{--                <br>you can continue your musical education with us without interuption.--}}
                <br>you can continue your access without interruption.
            </p>
        @endslot
    @endcomponent

    {{-- Post 90 Days Cancel Letter --}}
    @component('crux::partials._modal', ['modalId' => 'modal-post-90-day-cancel-letter'])
        @slot('contentSlot')
            <h1 class="heading tw-text-center">Uh oh! We???re here to help.</h1>

            <div class="tw-text-left tw-leading-6">
                <p class="mt-4">
                    When you joined {{ ucfirst($brand) }}, you made a decision to improve your skills. You were likely
                    excited and
                    gained a new sense of energy and inspiration. Take a moment to reflect on what happened since then.
                </p>

                <p class="mt-2">
                    Are you practicing less? Do you feel like you???ve hit a wall? Are you not sure what to practice next?
                </p>

                @if($brand == 'drumeo')

                    <p class="mt-2">
                        We???re committed to helping drummers reach their goals, so before you cancel your membership
                        I wanted to see if there???s any way we can help.
                    </p>

                    <p class="mt-2">
                        My advice: Don???t give up.</p>

                    <p class="mt-2">
                        The biggest difference between successful and unsuccessful drummers is the quality and quantity
                        of
                        action they take. By asking us for help, we???ll do our best to get you back on track towards your
                        biggest and smallest drumming goals. Just click the button to reach out.
                    </p>

                    <p class="mt-2">
                        To Your Drumming Success,
                    </p>

                @else

                    <p class="mt-2">
                        We???re committed to helping musicians reach their goals, so before you cancel your membership
                        I wanted to see if there???s any way we can help.
                    </p>

                    <p class="mt-2">
                        My advice: Don???t give up.</p>

                    <p class="mt-2">
                        The biggest difference between successful and unsuccessful musicians is the quality and quantity
                        of
                        action they take. By asking us for help, we???ll do our best to get you back on track towards your
                        biggest and smallest musical goals. Just click the button to reach out.
                    </p>

                    <p class="mt-2">
                        To Your Musical Success,
                    </p>

                @endif

                <div class="tw-flex tw-flex-row tw-mt-6">

                    @php
                        $portrait = \Railroad\Crux\Services\BrandSpecificResourceService::leadInstructorDetails($brand)['portrait'];
                        $signature = \Railroad\Crux\Services\BrandSpecificResourceService::leadInstructorDetails($brand)['signature'];
                        $name = \Railroad\Crux\Services\BrandSpecificResourceService::leadInstructorDetails($brand)['name'];
                    @endphp

                    <img
                            src="{{ imgix($portrait, ["q" => 80, "w" => 100, "h" => 100, "fit" => "fill", "auto" => "format"]) }}"
                            alt="{{ $name }} portrait">
                    <img
                            src="{{ imgix($signature, ["q" => 80, "w" => 100, "h" => 80, "fit" => "fill", "auto" => "format"]) }}"
                            alt="{{ $name }} signature"
                            class="tw-ml-6 tw-mt-4 tw-h-16">
                    <p class="tw-mt-10 tw-ml-6"> - {{ $name }}</p>

                </div>
            </div>

            {{-- this needs to close the current modal, then open the how can we help one --}}
            <a href="#"
               class="mu-close-modal-then-open-how-can-we-help tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-3 tw-pl-16 tw-pr-16 tw-text-white tw-rounded-full tw-mt-8 tw-text-sm">
                Yes, Please Help
            </a>

            @if(!empty($subscription))
                <a href="{{ url()->route('crux.cancel-reason-form')}}"
                   class="tw-uppercase tw-font-bold tw-no-underline tw-mt-6 text-{{ $brand }}">
                    No Thanks, Cancel Membership
                </a>
            @endif
        @endslot
    @endcomponent
@endsection
@extends('crux::win-back.side-by-side-layout', ['hideBreadcrumbs' => true, 'hideNav' => true, 'hideFooter' => true])

@section('meta')
    <title>Cancel | {{ ucfirst($brand) }}</title>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ mix('/tailwindcss/tailwind.css') }}">
@endsection

{{--@section('scripts')--}}
{{--    <script src="{{ mix('assets/members/js/profile.js') }}"></script>--}}
{{--@endsection--}}

@section('topFullWidthSection')
    <img src="{{ imgix(\Railroad\Crux\Services\BrandSpecificResourceService::logoUrl($brand), ["auto" => "format"]) }}"
         class="md:tw-w-1/2 tw-w-full">

    <h2 class="tw-text-lg tw-mt-14">Thanks for your feedback.</h2>

    <p class="tw-mt-4 tw-leading-7">We want to make sure {{ ucfirst($brand) }} works for you. And we’re here to
        help:</p>
@endsection

@section('leftBoxTopGraySection')
    <i class="fas fa-tag tw-text-white tw-text-3xl lg:tw-text-5xl tw-block tw-text-center tw-mt-3"></i>
    <h2 class="tw-text-white tw-text-center tw-mt-3 tw-text-xl lg:tw-text-2xl tw-mb-3">Keep Learning For Less</h2>
@endsection

@section('leftBoxBottomWhiteSection')

    <?php
    use Railroad\Crux\Services\BrandSpecificResourceService;$priceOfferCents = BrandSpecificResourceService::pricesOfferCents(
        $brand
    )['monthly'];
    $priceStandardCents = BrandSpecificResourceService::pricesStandardCents($brand)['monthly'];
    ?>

    <p class="">We love our students and REALLY want to keep you around! So we’re offering you a
        discounted monthly rate of just ${{ $priceOfferCents / 100 }}/month.
{{--        (Save {{ 100 - round(($priceOfferCents / $priceStandardCents) * 100) }}% compared to the normal rate of--}}
{{--        ${{ $priceStandardCents/100 }}).--}}
        (Save {{ $savingsOfOfferComparedToCurrent }}% compared to your current rate of ${{ $priceCurrent }}).
    </p>

    <p class="tw-mt-4">Your renewal date will not change and you’ll have access to
        this discount for as long as you remain a member.</p>

    <form method="post"
          action="{{ url()->route('crux.submit.accept-monthly-discount-rate-offer') }}">

        {{ csrf_field() }}

        <a href="#"
           class="tw-uppercase tw-block tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-2 tw-px-5 md:tw-px-12 tw-text-white tw-rounded-full tw-mt-10"
           onclick="this.parentNode.submit(); return false;">
            Continue My Membership
        </a>
    </form>
@endsection

@section('rightBoxTopGraySection')
    <i class="fas fa-pause tw-text-white tw-text-3xl lg:tw-text-5xl tw-block tw-text-center tw-mt-3"></i>
    <h2 class="tw-text-white tw-text-center tw-mt-3 tw-text-xl lg:tw-text-2xl tw-mb-3">Pause Your Membership</h2>
@endsection

@section('rightBoxBottomWhiteSection')
    <p class="">Sometimes you just need a break. Keep your progress-tracking and account history and
        pause your access for the next:</p>

    <form method="post"
          action="{{ url()->route('crux.submit.pause') }}"
          class="tw-flex tw-flex-col tw-flex-grow">

        {{ csrf_field() }}

        <div class="tw-flex lg:tw-flex-row md:tw-flex-col tw-flex-row tw-justify-between tw-mt-6 tw-px-10 md:tw-px-3">
            <label for="30-days" class="tw-mt-2">
                <input type="radio" id="30-days" value="30" name="amount-of-days" class="tw-mr-2" checked>
                30 Days
            </label>
            <label for="60-days" class="tw-mt-2">
                <input type="radio" id="60-days" value="60" name="amount-of-days" class="tw-mr-2">
                60 Days
            </label>
            <label for="90-days" class="tw-mt-2">
                <input type="radio" id="90-days" value="90" name="amount-of-days" class="tw-mr-2">
                90 Days
            </label>
        </div>

        <a href="#"
           class="tw-uppercase tw-block tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-2 tw-px-5 md:tw-px-12 tw-text-white tw-rounded-full tw-mt-10 md:tw-mt-auto"
           onclick="this.parentNode.submit(); return false;">
            Pause Membership
        </a>
    </form>
@endsection

@section('bottomFullWidthSection')
    <form method="post"
          action="{{ url()->route('crux.submit.cancel') }}"
          class="tw-max-w-full">

        {{ csrf_field() }}

        <a href="#"
           class="text-{{ $brand }} text-{{ $brand }}-hover-darken tw-uppercase tw-font-bold tw-no-underline tw-border-2 tw-border-solid tw-border-{{ $brand }} tw-p-4 tw-px-12 tw-rounded-full tw-mt-2 tw-w-full md:tw-w-1"
           onclick="this.parentNode.submit(); return false;" style="cursor:pointer">
            Finish Cancelling
        </a>
    </form>
@endsection
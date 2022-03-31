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
    <i class="far fa-life-ring tw-text-white tw-text-3xl lg:tw-text-5xl tw-block tw-text-center tw-mt-3"></i>
    <h2 class="tw-text-white tw-text-center tw-mt-3 tw-text-xl lg:tw-text-2xl tw-mb-3">Technical Support</h2>
@endsection

@section('leftBoxBottomWhiteSection')
    <p class="">We’re here to help you find the right lessons, solve technical issues,
        or answer any questions you may have.</p>

    <p class="tw-mt-4">We’re available to help by phone or email between 8AM to 4PM Pacific Time.</p>

    <a href="{{ url()->route('members.support') }}"
       class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{ \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand) }} tw-p-2 tw-px-5 md:tw-px-12 tw-text-white tw-rounded-full tw-mt-10">
        Contact {{ ucfirst($brand) }} Support
    </a>
@endsection

@section('rightBoxTopGraySection')
    <i class="fas fa-user-md-chat tw-text-white tw-text-3xl lg:tw-text-5xl tw-block tw-text-center tw-mt-3"></i>
    <h2 class="tw-text-white tw-text-center tw-mt-3 tw-text-xl lg:tw-text-2xl tw-mb-3">Student Care</h2>
@endsection

@section('rightBoxBottomWhiteSection')
    <p class="">Do you need to find the perfect lesson or get personal feedback on your playing?</p>

    <p class="tw-mt-4">Click the button to hear from a {{ ucfirst($brand) }} instructor who will guide and assist
        you.</p>

    <form method="post"
          action="{{ url()->route('crux.submit.add-student-plan-attribute') }}"
          class="tw-uppercase tw-font-bold tw-no-underline bg-{{ $brand }} hover:{{
    \Railroad\Crux\Services\BrandSpecificResourceService::styleHoverClass($brand)
    }} tw-p-2 tw-px-5 md:tw-px-12 tw-text-white tw-rounded-full tw-mt-10 md:tw-mt-auto">

        {{ csrf_field() }}

        <a href="#"
           class="tw-no-underline tw-text-white"
           onclick="this.parentNode.submit(); return false;">
            Get An Instructor
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
           onclick="this.parentNode.submit(); return false;">
            Finish Cancelling
        </a>
    </form>
@endsection
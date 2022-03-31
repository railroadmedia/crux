@extends('members.layout', ['hideBreadcrumbs' => true, 'hideNav' => true, 'hideFooter' => true])

@section('meta')
    <title>Cancel | {{ ucfirst($brand) }}</title>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ mix('/tailwindcss/tailwind.css') }}">
@endsection

@section('scripts')
    {{--    @if($brand == 'drumeo')--}}
    {{--        <script src="{{ mix('assets/members/js/profile.js') }}"></script>--}}
    {{--    @elseif--}}
    {{--        <script src="{{ mix('assets/members/js/profile.js') }}"></script>--}}
    {{--    @endif--}}
@endsection

@section('content')
    <div class="tw-container tw-mx-auto tw-max">
        <div class="tw-flex tw-flex-col tw-items-center tw-p-10 tw-max-w-screen-lg tw-mx-auto">

            @yield('topFullWidthSection')

            <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-10 tw-w-full">

                <div class="tw-flex tw-flex-col tw-items-end tw-w-full md:tw-w-1/2 md:tw-pr-4 tw-mb-10">

                    {{-- top gray section --}}
                    <div class="tw-bg-gray-500 tw-rounded-t-lg tw-px-10 tw-py-4 tw-w-full">
                        @yield('leftBoxTopGraySection')
                    </div>

                    {{-- bottom white section --}}
                    <div class="tw-flex tw-flex-col tw-border-solid tw-border-1 tw-border-t-0 tw-border-gray-200 tw-rounded-b-lg lg:tw-px-10 tw-px-5 lg:tw-py-12 tw-py-8 tw-w-full tw-text-center tw-leading-7 tw-flex-grow">
                        @yield('leftBoxBottomWhiteSection')
                    </div>
                </div>

                <div class="tw-flex tw-flex-col tw-items-start tw-w-full md:tw-w-1/2 md:tw-pl-4 tw-mb-10">

                    {{-- top gray section --}}
                    <div class="tw-bg-gray-500 tw-rounded-t-lg tw-px-10 tw-py-4 tw-w-full {{ $topRightBoxHeaderClassOverride ?? '' }}">
                        @yield('rightBoxTopGraySection')
                    </div>

                    {{-- bottom white section --}}
                    <div class="tw-flex tw-flex-col tw-border-solid tw-border-1 tw-border-t-0 tw-border-gray-200 tw-rounded-b-lg lg:tw-px-10 tw-px-5 lg:tw-py-12 tw-py-8 tw-w-full tw-text-center tw-leading-7 tw-flex-grow">
                        @yield('rightBoxBottomWhiteSection')
                    </div>
                </div>

            </div>

            @yield('bottomFullWidthSection')
        </div>

    </div>
@endsection


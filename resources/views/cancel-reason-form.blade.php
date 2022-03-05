@extends('members.layout', ['hideBreadcrumbs' => true, 'hideNav' => true, 'hideFooter' => true])

@section('meta')
    <title>Cancel | Drumeo</title>
@endsection

@section('styles')
    <link rel="stylesheet" href="/laravel/public/tailwindcss/tailwind.css">
@endsection

@section('scripts')

    <script src="{{ _mix('js/profile.js') }}"></script>

    <script type="text/javascript">
        const form = document.getElementById("cancel-reason-form");

        form.addEventListener('submit', function (event) {
            var reason = document.forms["cancel-reason-form"]["reason"].value;

            if (reason == "" || reason == undefined) {
                event.preventDefault();
                event.stopPropagation();

                document.getElementById('reason-validation-message').style.display = 'block';
                return false;
            }

            document.getElementById('reason-validation-message').style.display = 'none';
        });
    </script>

@endsection

@section('content')
    <div class="tw-container tw-mx-auto tw-max">
        <div class="tw-flex tw-flex-col tw-items-center tw-p-10 tw-max-w-screen-md tw-mx-auto">

            <img src="{{ imgix(
                            \Railroad\Crux\Services\BrandSpecificResourceService::logoUrl($brand),
                            ["auto" => "format"]
                        ) }}"
                 class="md:tw-w-1/2 tw-w-full">

            <h2 class="tw-text-3xl tw-mt-14 tw-text-center">Will you let us know why you're cancelling?</h2>

            <p class="tw-mt-6 tw-leading-7 tw-text-center">We’re sorry to hear that you’re unhappy with your membership.
                <br>If you have a minute, we’d like to know where we went wrong.</p>

            <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-5 tw-w-full">
                <form method="post"
                      action="{{ url()->route('crux.submit.cancel-reason') }}"
                      id="cancel-reason-form"
                      class="tw-flex tw-flex-col tw-flex-grow">

                    <div class="tw-flex tw-flex-col tw-flex-row tw-justify-between md:tw-ml-24 md:tw-px-10 md:tw-px-3">

                        @foreach((config('crux.reason-maps-by-brand')[$brand] ?? []) as $key => $wording)
                            <label for="{{ $key }}" class="tw-mt-4">
                                <input type="radio" id="{{ $key }}"
                                       value="{{ $key }}"
                                       name="reason" class="tw-mr-2">
                                {{ $wording }}
                            </label>
                        @endforeach

                        <textarea placeholder="Type your feedback here..."
                                  class="tw-mt-6 tw-rounded-lg md:tw-w-3/4"
                                  name="other-reason-text"></textarea>
                    </div>

                    <div class="tw-flex tw-flex-col tw-items-center tw-w-full tw-mt-10">
                        <p class="tw-text-red-600 tw-mb-4" id="reason-validation-message" style="display: none;">
                            <strong>Please select a reason.</strong>
                        </p>
                        <button
                                type="submit"
                                class="tw-uppercase tw-font-bold tw-no-underline tw-border-0 tw-text-white tw-bg-drumeo tw-p-4 tw-px-12 tw-rounded-full tw-mt-2 tw-w-full md:tw-w-1/2 tw-text-center">
                            Cancel Membership
                        </button>

                        <a href="{{ url()->route('crux.access-details') }}"
                           class="tw-uppercase tw-font-bold tw-no-underline tw-mt-6">
                            Go Back
                        </a>
                    </div>

                </form>

            </div>

            @yield('bottomFullWidthSection')
        </div>

    </div>
@endsection


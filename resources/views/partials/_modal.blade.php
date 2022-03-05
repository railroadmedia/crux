<div class="mu-modal {{ $modalId }} tw-opacity-0 tw-pointer-events-none tw-fixed tw-w-full tw-h-full tw-top-0 tw-left-0 tw-flex tw-items-center tw-justify-center tw-z-150 tw-overflow-auto">

    <div class="mu-modal-overlay tw-absolute tw-w-full tw-h-full tw-bg-gray-900 tw-opacity-75 tw-z-150"></div>

    <div class="mu-modal-container tw-bg-white tw-w-11/12 md:tw-max-w-2xl tw-mx-auto tw-rounded tw-shadow-lg tw-z-150 tw-overflow-y-auto tw-rounded-lg">

        <div class="mu-modal-close tw-absolute tw-top-0 tw-right-0 tw-cursor-pointer tw-flex tw-flex-col tw-items-center tw-mt-4 tw-mr-4 tw-text-white tw-text-sm z-50">
            <svg class="tw-fill-current tw-text-white" xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                 viewBox="0 0 18 18">
                <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
            </svg>
        </div>

        <div class="mu-modal-content tw-py-12 tw-text-left tw-px-12 tw-flex tw-flex-col tw-items-center tw-justify-center tw-relative">

            <!-- Close -->
            <div class="tw-flex tw-flex-row tw-justify-between tw-items-center tw-pb-3 tw-absolute tw-z-250 tw-right-1 tw-top-1">
                <div class="mu-modal-close tw-cursor-pointer tw-z-50 tw-items-end">
                    <svg class="tw-fill-current tw-text-gray-500" xmlns="http://www.w3.org/2000/svg" width="22"
                         height="22" viewBox="0 0 18 18">
                        <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                    </svg>
                </div>
            </div>

            {{ $contentSlot }}
        </div>
    </div>
</div>
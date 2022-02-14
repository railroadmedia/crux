<?php

use App\Http\Controllers\Profiles\CancellationController;
use App\Http\Controllers\Profiles\RetentionOfferController;
use Illuminate\Support\Facades\Route;
use Railroad\Crux\Http\Controllers\AccountDetailsController;

Route::group(
    [
        'middleware' => ['web', 'auth'],
        'prefix' => '/members/crux'
    ],
    function () {

        Route::get(
            '/foo',
            [
                'as' => 'members.crux.foo',
                //'uses' => AccountDetailsController::class . '@accountDetails'
                function(){
                    dd('foooo!');
                }
            ]
        );

//        // cancellation pages
//        Route::get(
//            '/cancel/annual-offer',
//            [
//                'as' => 'user.settings.cancel.annual-offer',
//                'uses' => CancellationController::class . '@viewAnnualOffer'
//            ]
//        );
//
//        Route::get(
//            '/cancel/monthly-offer',
//            [
//                'as' => 'user.settings.cancel.monthly-offer',
//                'uses' => CancellationController::class . '@viewMonthlyOffer'
//            ]
//        );
//
//        Route::get(
//            '/cancel/student-care',
//            [
//                'as' => 'user.settings.cancel.student-care',
//                'uses' => CancellationController::class . '@viewStudentCare'
//            ]
//        );
//
//        Route::get(
//            '/cancel/cancel-reason-form',
//            [
//                'as' => 'user.settings.cancel.cancel-reason-form',
//                'uses' => CancellationController::class . '@viewCancelReasonForm'
//            ]
//        );
//
//        // retention email offer
//
//        Route::get(
//            '/cancel/email-offer/{offer?}',
//            [
//                'as' => 'user.settings.cancel.retention-email-offer',
//                'uses' => RetentionOfferController::class . '@retentionEmailOffer'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/email-offer/{offer?}',
//            [
//                'as' => 'user.settings.cancel.submit.retention-email-offer',
//                'uses' => RetentionOfferController::class . '@acceptRetentionEmailOffer'
//            ]
//        );
//
//        // form submission endpoints
//
//        // "Part 3 - Account Details Page Actions" from Cancellation Project notes at following link:
//        // https://docs.google.com/document/d/143SWRqh0rUhj8fUuJCVrgpjQXefw2BOTiBToMscKzYE/edit#heading=h.2lyalcvb2uaf
//
//// todo: delete, this is handled via the order form
////        Route::post(
////            '/cancel/submit/pack-owner-trial-offer',
////            [
////                'as' => 'user.settings.cancel.submit.pack-owner-trial-offer',
////                'uses' => CancellationController::class . '@packOwnerTrialOffer'
////            ]
////        );
//
//        Route::post(
//            '/cancel/submit/feedback',
//            [
//                'as' => 'user.settings.cancel.submit.feedback',
//                'uses' => CancellationController::class . '@submitFeedback'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/accept-trial-extension-offer',
//            [
//                'as' => 'user.settings.cancel.submit.accept-trial-extension-offer',
//                'uses' => CancellationController::class . '@acceptTrialExtensionOffer'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/submit-cancel-reason',
//            [
//                'as' => 'user.settings.cancel.submit.submit-cancel-reason',
//                'uses' => CancellationController::class . '@submitCancelReason'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/accept-month-extension-offer',
//            [
//                'as' => 'user.settings.cancel.submit.accept-month-extension-offer',
//                'uses' => CancellationController::class . '@acceptMonthExtensionOffer'
//            ]
//        );
//
//        // "Part 4 - Cancellation Page" from Cancellation Project notes at following link:
//        // https://docs.google.com/document/d/143SWRqh0rUhj8fUuJCVrgpjQXefw2BOTiBToMscKzYE/edit#heading=h.8hnhdvh85zm7
//
//        Route::post(
//            '/cancel/submit/cancel',
//            [
//                'as' => 'user.settings.cancel.submit.cancel',
//                'uses' => CancellationController::class . '@cancel'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/accept-monthly-discount-rate-offer',
//            [
//                'as' => 'user.settings.cancel.submit.accept-monthly-discount-rate-offer',
//                'uses' => CancellationController::class . '@acceptMonthlyDiscountRateOffer'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/accept-downgrade-to-monthly-billing-offer',
//            [
//                'as' => 'user.settings.cancel.submit.accept-downgrade-to-monthly-billing-offer',
//                'uses' => CancellationController::class . '@acceptMonthlyDiscountRateOffer'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/pause',
//            [
//                'as' => 'user.settings.cancel.submit.pause',
//                'uses' => CancellationController::class . '@pause'
//            ]
//        );
//
//        Route::post(
//            '/cancel/submit/send-help-email',
//            [
//                'as' => 'user.settings.cancel.submit.send-help-email',
//                'uses' => CancellationController::class . '@sendHelpEmail'
//            ]
//        );
    }
);

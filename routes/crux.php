<?php

use Illuminate\Support\Facades\Route;
use Railroad\Crux\Http\Controllers\ViewController;
use Railroad\Crux\Http\Controllers\ActionController;

Route::group(
    [
        'middleware' => config('crux.middleware'),
        'prefix' => '/members/access'
    ],
    function () {

        Route::get(
            '/details',
            [
                'as' => 'crux.access-details',
                'uses' => ViewController::class . '@accessDetails'
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
        Route::get(
            '/cancel-reason-form',
            [
                'as' => 'crux.cancel-reason-form',
                'uses' => ViewController::class . '@viewCancelReasonForm'
            ]
        );
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

        Route::post(
            '/submit/feedback',
            [
                'as' => 'crux.submit.feedback',
                'uses' => ActionController::class . '@submitFeedback'
            ]
        );

        Route::post(
            '/submit/accept-trial-extension-offer',
            [
                'as' => 'crux.submit.accept-trial-extension-offer',
                'uses' => ActionController::class . '@acceptTrialExtensionOffer'
            ]
        );

        Route::post(
            '/submit/cancel-reason',
            [
                'as' => 'crux.submit.cancel-reason',
                'uses' => ActionController::class . '@submitCancelReason'
            ]
        );

        Route::post(
            '/submit/accept-month-extension-offer',
            [
                'as' => 'crux.submit.accept-month-extension-offer',
                'uses' => ActionController::class . '@acceptMonthExtensionOffer'
            ]
        );
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
        Route::post(
            '/submit/send-help-email',
            [
                'as' => 'crux.submit.send-help-email',
                'uses' => ActionController::class . '@sendHelpEmail'
            ]
        );
    }
);

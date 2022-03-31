<?php

use Illuminate\Support\Facades\Route;
use Railroad\Crux\Http\Controllers\ViewController;
use Railroad\Crux\Http\Controllers\ActionController;

Route::group(
    [
        'middleware' => config('crux.middleware'),
        'prefix' => '/members/settings/access'
    ],
    function () {
        Route::get(
            '/',
            [
                'as' => 'crux.access-details',
                'uses' => ViewController::class . '@accessDetails'
            ]
        );

//        // cancellation pages
        Route::get(
            '/annual-offer',
            [
                'as' => 'crux.win-back.annual-offer',
                'uses' => ViewController::class . '@viewAnnualOffer'
            ]
        );

        Route::get(
            '/monthly-offer',
            [
                'as' => 'crux.win-back.monthly-offer',
                'uses' => ViewController::class . '@viewMonthlyOffer'
            ]
        );

        Route::get(
            '/student-care',
            [
                'as' => 'crux.win-back.student-care',
                'uses' => ViewController::class . '@viewStudentCare'
            ]
        );

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
            '/submit/cancel-reason',
            [
                'as' => 'crux.submit.cancel-reason',
                'uses' => ActionController::class . '@submitCancelReason'
            ]
        );

        Route::post(
            '/submit/accept-extension-offer',
            [
                'as' => 'crux.submit.accept-extension-offer',
                'uses' => ActionController::class . '@acceptExtensionOffer'
            ]
        );

        Route::post(
            '/submit/accept-extension-offer/{two_weeks?}',
            [
                'as' => 'crux.submit.accept-extension-offer',
                'uses' => ActionController::class . '@acceptExtensionOffer'
            ]
        );

        Route::post(
            '/submit/resume-paused',
            [
                'as' => 'crux.submit.resume-paused',
                'uses' => ActionController::class . '@resumePaused'
            ]
        );

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        Route::post(
            '/cancel/submit/cancel',
            [
                'as' => 'crux.submit.cancel',
                'uses' => ActionController::class . '@cancel'
            ]
        );

        Route::post(
            '/cancel/submit/accept-monthly-discount-rate-offer',
            [
                'as' => 'crux.submit.accept-monthly-discount-rate-offer',
                'uses' => ActionController::class . '@acceptMonthlyDiscountRateOffer'
            ]
        );

        Route::post(
            '/cancel/submit/accept-downgrade-to-monthly-billing-offer',
            [
                'as' => 'crux.submit.accept-downgrade-to-monthly-billing-offer',
                'uses' => ActionController::class . '@acceptMonthlyDiscountRateOffer'
            ]
        );

        Route::post(
            '/cancel/submit/pause',
            [
                'as' => 'crux.submit.pause',
                'uses' => ActionController::class . '@pause'
            ]
        );

        Route::post(
            '/submit/send-help-email',
            [
                'as' => 'crux.submit.send-help-email',
                'uses' => ActionController::class . '@sendHelpEmail'
            ]
        );

        Route::post(
            '/cancel/submit/add-student-plan',
            [
                'as' => 'crux.submit.add-student-plan-attribute',
                'uses' => ActionController::class . '@AddStudentPlanAttributeToCurrentUser'
            ]
        );
    }
);

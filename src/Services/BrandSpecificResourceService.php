<?php

namespace Railroad\Crux\Services;

use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Repositories\ProductRepository;

class BrandSpecificResourceService
{
    /**
     * @param $brand
     * @return string|void
     */
    public static function logoUrl($brand)
    {
        /*
         * available files (in musora-ui.s3.amazonaws.com/logos/, as of March 9th 2022)
         *
         *      drumeo-black.svg
         *      drumeo-edge-white.svg
         *      drumeo-white.svg
         *      drumeo.svg
         *
         *      guitareo_method_logo.svg
         *      guitareo-black.svg
         *      guitareo-white.svg
         *      guitareo.svg
         *
         *      musora-black.svg
         *      musora-white.svg
         *
         *      pianote-black.svg
         *      pianote-white.svg
         *      pianote.svg
         *
         *      recordeo-black.svg
         *      recordeo-white.svg
         *      recordeo.svg
         *
         *      singeo-black.svg
         *      singeo-method.svg
         *      singeo-white.svg
         *      singeo.svg
         */

        switch (strtolower($brand)) {
            case 'drumeo':
                return 'https://musora-ui.s3.amazonaws.com/logos/drumeo.svg';
            case 'pianote':
                return 'https://musora-ui.s3.amazonaws.com/logos/pianote.svg';
            case 'guitareo':
                return 'https://musora-ui.s3.amazonaws.com/logos/guitareo.svg';
            case 'singeo':
                return 'https://musora-ui.s3.amazonaws.com/logos/singeo.svg';
        }
    }

    public static function featureList($brand)
    {
        $default = [
            'Step-by-step curriculum.',
            'Courses from legendary teachers.',
            'Entertaining shows and documentaries.',
            'Song breakdowns & Play-Alongs.',
            'Live lessons and personal support.',
        ];

        switch (strtolower($brand)) {
            case 'drumeo':
                return [
                    ucfirst($brand) . ' Method step-by-step curriculum.',
                    '200+ courses from legendary teachers.',
                    'Entertaining shows and documentaries.',
                    'Song breakdowns & Play-Alongs.',
                    'Weekly live lessons and personal support.',
                ];
//            case 'pianote':
//                return $default; // todo
//            case 'guitareo':
//                return $default; // todo
//            case 'singeo':
//                return $default; // todo
        }

        return $default;
    }

    public static function brandColour($brand)
    {
        switch (strtolower($brand)) {
            case 'drumeo':
                return '0b76db';
            case 'pianote':
                return 'f61a30';
            case 'guitareo':
                return '00C9AC';
            case 'singeo':
                return '8300E9';
        }

        return '0b76db';
    }

    public static function styleHoverClass($brand)
    {
        switch (strtolower($brand)) {
            case 'drumeo':
                return 'tw-bg-blue-600';
            case 'pianote':
                return 'tw-bg-red-600';
            case 'guitareo':
                return 'tw-bg-green-600';
            case 'singeo':
                return 'tw-bg-purple-600';
        }

        return 'tw-bg-blue-600';
    }

    public static function styleBorderClass($brand)
    {
        switch (strtolower($brand)) {
            case 'drumeo':
                return 'tw-border-blue-500';
            case 'pianote':
                return 'tw-border-red-500';
            case 'guitareo':
                return 'tw-border-green-500';
            case 'singeo':
                return 'tw-border-purple-500';
        }

        return 'tw-border-blue-500';
    }

    public static function pricesStandardCents($brand)
    {
        $repo = app(ProductRepository::class);

        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
        ### ez query to double-check ###
        select id, sku from musora_laravel.ecommerce_products where id in (
            125, # DLM-1-year
            124, # DLM-1-month
            6, # PIANOTE-MEMBERSHIP-1-YEAR
            5, # PIANOTE-MEMBERSHIP-1-MONTH
            18, # GUITAREO-1-YEAR-MEMBERSHIP
            17, # GUITAREO-1-MONTH-MEMBERSHIP
            410, # singeo-annual-recurring-membership
            409 # singeo-monthly-recurring-membership
        )
         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

        switch ($brand) {
            case 'drumeo':
                $priceStandardCentsAnnual = ((int)$repo->findProduct(125)->getPrice(
                    )) * 100; // 125, DLM-1-year
                $priceStandardCentsMonthly = ((int)$repo->findProduct(124)->getPrice(
                    )) * 100; // 124, DLM-1-month
                break;
            case 'pianote':
                $priceStandardCentsAnnual = ((int)$repo->findProduct(6)->getPrice(
                    )) * 100; // 6, PIANOTE-MEMBERSHIP-1-YEAR
                $priceStandardCentsMonthly = ((int)$repo->findProduct(5)->getPrice(
                    )) * 100; // 5, PIANOTE-MEMBERSHIP-1-MONTH
                break;
            case 'guitareo':
                $priceStandardCentsAnnual = ((int)$repo->findProduct(18)->getPrice(
                    )) * 100; // 18, GUITAREO-1-YEAR-MEMBERSHIP
                $priceStandardCentsMonthly = ((int)$repo->findProduct(17)->getPrice(
                    )) * 100; // 17, GUITAREO-1-MONTH-MEMBERSHIP
                break;
            case 'singeo':
                $priceStandardCentsAnnual = ((int)$repo->findProduct(410)->getPrice(
                    )) * 100; // 410, singeo-annual-recurring-membership
                $priceStandardCentsMonthly = ((int)$repo->findProduct(409)->getPrice(
                    )) * 100; // 409, singeo-monthly-recurring-membership
                break;
        }

        return ['annual' => $priceStandardCentsAnnual, 'monthly' => $priceStandardCentsMonthly];
    }

    public static function pricesOfferCents($brand)
    {
        switch ($brand) {
            case 'drumeo':
                return ['annual' => 19700, 'monthly' => 1900];
            case 'pianote':
                return ['annual' => 19700, 'monthly' => 1900];
            case 'guitareo':
                return ['annual' => 12700, 'monthly' => 1449];
            case 'singeo':
                return ['annual' => 12700, 'monthly' => 1449];
        }

        return false;
    }

    /**
     * @param $brand
     * @return array
     */
    public static function leadInstructorDetails($brand)
    {
        $lisaSignature = 'https://d1923uyy6spedc.cloudfront.net/signature-lisa.png';

        if ($brand == 'pianote') {
            return [
                'portrait' => 'https://d1923uyy6spedc.cloudfront.net/headshot-circle-300-lisa.png',
                'signature' => $lisaSignature,
                'name' => 'Lisa Witt'
            ];
        } else if($brand == 'guitareo') {
            return [
                'portrait' => 'https://d1923uyy6spedc.cloudfront.net/headshot-circle-300-ayla.png',
                'signature' => 'https://d1923uyy6spedc.cloudfront.net/signature-ayla.png',
                'name' => 'Ayla Tesler-Mabe'
            ];
        } else if($brand == 'singeo') {
            return [
                'portrait' => 'https://d1923uyy6spedc.cloudfront.net/headshot-circle-300-lisa.png',
                'signature' => $lisaSignature,
                'name' => 'Lisa Witt'
            ];
        }

        return [
            'portrait' => 'https://d1923uyy6spedc.cloudfront.net/jared.png',
            'signature' => 'https://d1923uyy6spedc.cloudfront.net/jared-sig.jpg',
            'name' => 'Jared Falk'
        ];
    }

    public static function headerBackground($brand)
    {
        if ($brand == 'pianote') {
            return 'https://d2vyvo0tyx8ig5.cloudfront.net/backgrounds/default-3840.jpg';
        } else if($brand == 'guitareo') {
            return 'https://dmmior4id2ysr.cloudfront.net/assets/images/guitareo-header.jpg';
        } else if($brand == 'singeo') {
            return 'https://singeo.s3.amazonaws.com/singeo-header-image.jpg';
        }

        return 'https://dmmior4id2ysr.cloudfront.net/assets/images/drumeo-members-header-background-image.jpg';
    }

    // =================================================================================================================

    /**
     * @param $brand
     * @param $subscription
     * @return array
     */
    public static function savingsInfo($brand, $subscription)
    {
        // ----------------------------------------------------------------------------------------------------------
        // $presentWinbackAnnualOffer ------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------

//        $isAnnualSubscription = $subscription->getIntervalType() == 'year';
//        if ($isAnnualSubscription) {
//            $monthlyPriceInCents = $subscription->getTotalPrice() * 100;
//            $pricesOfferCentsAnnual = self::pricesOfferCents($brand)['annual'];
//            $savingsOfCurrentOverOffer = (int) round((1 - ($monthlyPriceInCents / $pricesOfferCentsAnnual)) * 100);
//            $doNotPresentWinbackAnnualOffer = $savingsOfCurrentOverOffer > 0;
//        }

        // ----------------------------------------------------------------------------------------------------------
        // $presentWinbackMonthlyOffer ------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------

        $isMonthlySubscription = ($subscription->getIntervalType() == 'month') && ($subscription->getIntervalCount() == 1);
        if ($isMonthlySubscription) {

            $savingsOfAnnualOverMonthly = self::determineSavingsOfAnnualOverMonthly($brand, $subscription);

            $pointThatSavingsGoFromAdvantageousToInsufficient = 10;

            if ($savingsOfAnnualOverMonthly < $pointThatSavingsGoFromAdvantageousToInsufficient) {
                $savingsOfAnnualOverMonthly = $savingsOfAnnualOverMonthly * -1;
                $savingsOverAnnual = round((1 - (100 / ($savingsOfAnnualOverMonthly + 100))) * 100);
                $pointThatSavingsGoFromAdvantageousToAmazing = 20;
                $insufficientSavingsToJustifyAnnualOffer = true;
                $savingsOverAnnualAreGood =
                    ($savingsOverAnnual >= $pointThatSavingsGoFromAdvantageousToInsufficient)
                    &&
                    ($savingsOverAnnual < $pointThatSavingsGoFromAdvantageousToAmazing);
                $savingsOverAnnualAreAmazing = $savingsOverAnnual >= $pointThatSavingsGoFromAdvantageousToAmazing;
                $savingsVsStandardMonthly = self::determineSavingsOfMonthlyVsStandardMonthly($brand, $subscription);
            }

            $monthlyPriceInCents = $subscription->getTotalPrice() * 100;
            $pricesOfferCentsMonthly = self::pricesOfferCents($brand)['monthly'];
            $savingsOfOfferComparedToCurrent = (int) round((1 - $pricesOfferCentsMonthly / $monthlyPriceInCents) * 100);
            $savingsOfCurrentComparedToOffer = (int) round((1 - $monthlyPriceInCents / $pricesOfferCentsMonthly) * 100);
            $winbackMonthlyOfferIsInsufficient = $savingsOfCurrentComparedToOffer >= 0;
        }

        return [
            'savingsOfAnnualOverMonthly' => $savingsOfAnnualOverMonthly ?? null,
            'insufficientSavingsToJustifyAnnualOffer' => $insufficientSavingsToJustifyAnnualOffer ?? false,
            'savingsOverAnnual' => $savingsOverAnnual ?? null,
            'savingsOverAnnualAreGood' => $savingsOverAnnualAreGood ?? null,
            'savingsOverAnnualAreAmazing' => $savingsOverAnnualAreAmazing ?? null,
            'savingsVsStandardMonthly' => $savingsVsStandardMonthly ?? null,
            'winbackMonthlyOfferIsInsufficient' => $winbackMonthlyOfferIsInsufficient ?? false,
            'savingsOfOfferComparedToCurrent' => $savingsOfOfferComparedToCurrent ?? null
        ];
    }

    /**
     * @param $brand
     * @param Subscription|null $subscription
     * @return float|int
     */
    private static function determineSavingsOfAnnualOverMonthly($brand, ?Subscription $subscription)
    {
        $pricesStandardCents = BrandSpecificResourceService::pricesStandardCents($brand);

        $monthlyPriceTimesTwelveForSavingsValue = $pricesStandardCents['monthly']  * 12;

        if ($subscription) {
            $isMonthlySubscription = ($subscription->getIntervalType() == 'month') && ($subscription->getIntervalCount() == 1);

            if ($isMonthlySubscription) {
                $monthlyPriceInCents = ((int) $subscription->getTotalPrice()) * 100;
                $monthlyPriceTimesTwelveForSavingsValue = $monthlyPriceInCents * 12;
            }
        }

        $ratioRaw = $pricesStandardCents['annual'] / $monthlyPriceTimesTwelveForSavingsValue;
        $ratioAdjusted = $ratioRaw * 100;
        $percentageRaw = 100 - $ratioAdjusted;

        return round($percentageRaw);
    }

    /**
     * @param $brand
     * @param Subscription|null $subscription
     * @return float|int
     */
    private static function determineSavingsOfMonthlyVsStandardMonthly($brand, ?Subscription $subscription)
    {
        $pricesStandardCents = BrandSpecificResourceService::pricesStandardCents($brand);

        $standardPriceInCents = $pricesStandardCents['monthly'];

        if ($subscription) {
            $isMonthlySubscription = ($subscription->getIntervalType() == 'month') && ($subscription->getIntervalCount() == 1);

            if ($isMonthlySubscription) {
                $monthlyPriceInCents = $subscription->getTotalPrice() * 100;
                $savings = (int) round((1 - ($monthlyPriceInCents / $standardPriceInCents)) * 100);
            }
        }

        return $savings ?? null;
    }
}
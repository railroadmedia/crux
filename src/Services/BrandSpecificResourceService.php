<?php

namespace Railroad\Crux\Services;

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

        switch(strtolower($brand)){
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

        switch(strtolower($brand)){
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
        switch(strtolower($brand)){
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
        switch(strtolower($brand)){
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
}
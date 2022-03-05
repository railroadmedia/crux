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
         * Other images used elsewhere noted here in case we need them later:
         * -------------------------------------------------------------------
         *
         * used for Drumeo account-details page but no longer because image has "Edge" and iifc we're no longer using that
         * https://drumeo-assets.s3.amazonaws.com/logos/edge-logo_small.png
         *
         * used for Drumeo email feedback-from-cancellation-workflow and other similar emails:
         * https://s3.amazonaws.com/drumeoblog/blog/wp-content/uploads/2016/07/drumeo-logo-verysmall.png
         */

        switch(strtolower($brand)){
            case 'drumeo':
                return 'https://dpwjbsxqtam5n.cloudfront.net/logos/logo-blue.png';
            case 'pianote':
                return 'https://pianote.s3.amazonaws.com/logo/pianote-logo-red.png';
            case 'guitareo':
                return 'https://musora-ui.s3.amazonaws.com/logos/guitareo.svg';
            case 'singeo':
                return 'https://singeo.s3.amazonaws.com/sales/2021/singeo-logo.png';
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
            case 'pianote':
                return $default; // todo
            case 'guitareo':
                return $default; // todo
            case 'singeo':
                return $default; // todo
        }

        return $default;
    }
}
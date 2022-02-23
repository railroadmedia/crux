<?php

namespace Railroad\Crux\Services;

use App\Services\User\UserAccessService;

class NavigationSpecificsDeterminationService
{
    public static function settingSections($section)
    {
        /* * * * * * * * * * *
        profile
        login-credentials
        payments
        settings
        access-details
        * * * * * * * * * * */

        $brand = config('railcontent.brand');

        $settingsSections = [
            [
                "url" => self::getUrlForSection('profile'),
                "icon" => "fas fa-edit",
                "title" => "Profile",
                "active" => strpos(url()->current(), 'profile'),
            ],
            [
                "url" => self::getUrlForSection('login-credentials'),
                "icon" => "fas fa-lock",
                "title" => "Login Credentials",
                "active" => strpos(url()->current(), 'login'),
            ],
            [
                "url" => self::getUrlForSection('payments'),
                "icon" => "far fa-credit-card",
                "title" => "Payments",
                "active" => strpos(url()->current(), 'payments'),
            ],
            [
                "url" => self::getUrlForSection('settings'),
                "icon" => "fas fa-bell",
                "title" => "Settings",
                "active" => (array_reverse(request()->segments())[0] ?? '') == 'settings',
            ],
            [
                "url" => self::getUrlForSection('access.details'),
                "icon" => "fas fa-calendar-alt",
                "title" => "Account Details",
                "active" => strpos(url()->current(), 'account.details'),
            ],
        ];

        if($brand == 'drumeo'){
            // hide magazine settings for non-members
            if (!empty(current_user()) && !UserAccessService::isMember(current_user()->getId())) {
                unset($settingsSections[5]);
            }
        }

        return $settingsSections;
    }

    public static function getUrlForSection($section)
    {
        $brand = config('railcontent.brand');

        switch($section) {
            case 'profile':
                switch($brand){
                    case 'drumeo':
                        return url()->route('user.settings.profile');
                    case 'pianote':
                        return url()->route('members.profile.settings');
                    case 'guitareo':
                        return url()->route('members.account.settings');
                    case 'singeo':
                        return url()->route('members.profile.settings');
                }

            case 'login-credentials':
                switch($brand){
                    case 'drumeo':
                        return url()->route('user.settings.login-credentials');
                    case 'pianote':
                        return url()->route('members.profile.settings', ['section' => 'login-credentials']);
                    case 'guitareo':
                        return url()->route('members.account.settings.login-credentials');
                    case 'singeo':
                        return url()->route('members.profile.settings',['section' => 'login-credentials']);
                }

            case 'payments':
                switch($brand){
                    case 'drumeo':
                        return url()->route('user.settings.payments');
                    case 'pianote':
                        return url()->route('members.profile.settings', ['section' => 'payments']);
                    case 'guitareo':
                        return url()->route('members.account.settings.payments');
                    case 'singeo':
                        return url()->route('members.profile.settings',['section' => 'payments']);
                }

            case 'settings':
                switch($brand){
                    case 'drumeo':
                        return url()->route('user.settings.settings');
                    case 'pianote':
                        #return url()->route('members.profile.settings', ['section' => 'settings']);
                        return url()->route('members.profile.settings.settings'); # does this work? If so probably use this rather than the commented-out line above
                    case 'guitareo':
                        return url()->route('members.account.settings.settings');
                    case 'singeo':
                        return url()->route('members.profile.settings',['section' => 'settings']);
                        #return url()->route('members.profile.settings.settings'); # does this work? If so probably use this rather than the commented-out line above
                }

            case 'access':
                return url()->route('members.crux.access.details');
        }

    }
}
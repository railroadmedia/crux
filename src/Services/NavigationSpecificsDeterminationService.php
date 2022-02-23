<?php

namespace Railroad\Crux\Services;

use App\Services\User\UserAccessService;

class NavigationSpecificsDeterminationService
{
    public static function settingSections($section)
    {
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
                "url" => self::getUrlForSection('access'),
                "icon" => "fas fa-calendar-alt",
                "title" => "Access",
                "active" => strpos(url()->current(), 'access'),
            ],
        ];

        return $settingsSections;
    }

    /**
     * @param $section
     * @return string
     * @throws \Exception
     */
    public static function getUrlForSection($section)
    {
        $brand = config('railcontent.brand');

        switch ($section) {
            case 'profile':
                switch ($brand) {
                    case 'drumeo':
                        return url()->route('user.settings.profile');
                    case 'pianote':
                    case 'singeo':
                        return url()->route('members.profile.settings');
                    case 'guitareo':
                        return url()->route('members.account.settings');
                }
                break;

            case 'login-credentials':
                switch ($brand) {
                    case 'drumeo':
                        return url()->route('user.settings.login-credentials');
                    case 'pianote':
                    case 'singeo':
                        return url()->route('members.profile.settings', ['section' => 'login-credentials']);
                    case 'guitareo':
                        return url()->route('members.account.settings.login-credentials');
                }
                break;

            case 'payments':
                switch ($brand) {
                    case 'drumeo':
                        return url()->route('user.settings.payments');
                    case 'pianote':
                    case 'singeo':
                        return url()->route('members.profile.settings', ['section' => 'payments']);
                    case 'guitareo':
                        return url()->route('members.account.settings.payments');
                }
                break;
            case 'settings':
                switch ($brand) {
                    case 'drumeo':
                        return url()->route('user.settings.settings');
                    case 'pianote':
                        return url()->route('members.profile.settings.settings');
                    case 'guitareo':
                        return url()->route('members.account.settings.settings');
                    case 'singeo':
                        return url()->route('members.profile.settings', ['section' => 'settings']);
                }
                break;
            case 'access':
                return url()->route('members.crux.access.details');
        }

        throw new \Exception('unexpected $section value "' . $section . '" not found for brand ' . $brand);
    }
}
<?php

namespace Railroad\Crux\Services;

use App\Services\User\UserAccessService;
use Illuminate\Support\Facades\Route;

class NavigationSpecificsDeterminationService
{
    public static function settingSections($section)
    {
        $name = Route::getCurrentRoute()->getName();

        try {
            $settingsSections = [
                [
                    "url" => NavigationSpecificsDeterminationService::getUrlForSection('profile'),
                    "icon" => "fas fa-edit",
                    "title" => "Profile",
                    "active" => (
                        NavigationSpecificsDeterminationService::getRouteNameForSection('profile') ==  $name
                    )
                ],
                [
                    "url" => NavigationSpecificsDeterminationService::getUrlForSection('login-credentials'),
                    "icon" => "fas fa-lock",
                    "title" => "Login Credentials",
                    "active" => (
                        NavigationSpecificsDeterminationService::getRouteNameForSection('login-credentials') ==  $name
                    )
                ],
                [
                    "url" => NavigationSpecificsDeterminationService::getUrlForSection('payments'),
                    "icon" => "far fa-credit-card",
                    "title" => "Payments",
                    "active" => (
                        NavigationSpecificsDeterminationService::getRouteNameForSection('payments') ==  $name
                    )
                ],
                [
                    "url" => NavigationSpecificsDeterminationService::getUrlForSection('technology'),
                    "icon" => "fas fa-bell",
                    "title" => "Technology",
                    "active" => (
                        NavigationSpecificsDeterminationService::getRouteNameForSection('technology') ==  $name
                    )
                ],
                [
                    "url" => NavigationSpecificsDeterminationService::getUrlForSection('access'),
                    "icon" => "fas fa-calendar-alt",
                    "title" => "Access",
                    "active" => (
                        NavigationSpecificsDeterminationService::getRouteNameForSection('access') ==  $name
                    )
                ],
            ];
        } catch (\Exception $exception) {
            error_log($exception);
            return [];
        }

        // hide magazine settings for non-members
        if (!empty(current_user()) && !UserAccessService::isMember(current_user()->getId())) {
            unset($settingsSections[5]);
        }

        return $settingsSections;
    }

    /**
     * @param $section
     * @return string
     * @throws \Exception
     */
    public static function getUrlForSection($section)
    {
        switch ($section) {
            case 'profile':
                return url()->route(self::getRouteNameForSection('profile'));
            case 'login-credentials':
                return url()->route(self::getRouteNameForSection('login-credentials'));
            case 'payments':
                return url()->route(self::getRouteNameForSection('payments'));
            case 'technology':
                return url()->route(self::getRouteNameForSection('technology'));
            case 'access':
                return url()->route(self::getRouteNameForSection('access'));
        }

        throw new \Exception('unexpected $section value "' . $section . '" not found.');
    }

    /**
     * @param $section
     * @return string
     * @throws \Exception
     */
    public static function getRouteNameForSection($section)
    {
        switch ($section) {
            case 'profile':
                return 'members.settings.profile';
            case 'login-credentials':
                return 'members.settings.login-credentials';
            case 'payments':
                return 'members.settings.payments';
            case 'technology':
                return 'members.settings.technology';
            case 'access':
                return 'crux.access-details';
        }

        throw new \Exception('unexpected $section value "' . $section . '" not found.');
    }
}
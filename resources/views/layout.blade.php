@php
    $bodyClass = ($bodyClass ?? '') . ' sidebar';
    $leftSidebar = true
@endphp

@extends('members.layout')

@section('style')

    <style>
        .renew-link:hover .btn span.bg-success {
            background-color: #13E868;
        }

        .mu-modal {
            transition: opacity 0.25s ease;
        }

        body.mu-modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }

        .text-drumeo {
            color: #0b76db;
        }

        .text-pianote {
            color: #f61a30;
        }

        .text-guitareo {
            color: #00C9AC;
        }

        .text-singeo {
            color: #8300E9;
        }
    </style>
@endsection

@section('content')
    @if($brand == 'drumeo')
        @include('members.partials._drumeo-sidebar')
    @else
        @include('members.partials._content-sidebar')
    @endif

    @include('bladesora::members.partials._account-header', [
        "backgroundImage" => \Railroad\Crux\Services\BrandSpecificResourceService::headerBackground($brand),
        "userAvatar" => current_user()->getProfilePictureUrl(),
        "userName" => current_user()->getDisplayName(),
        "appName" => ucfirst($brand),
        "memberSince" => current_user()->getCreatedAt()
    ])

    @if(session()->has('success'))
        <div class="form-success-message container mt-3">
            <div class="flex flex-column bg-success shadow corners-10 pa">
                <p class="body text-white">Profile successfully updated!</p>
            </div>
        </div>
    @endif

    @if(session()->has('successes'))
        <div class="form-success-message container mt-3">
            <div class="flex flex-column bg-success shadow corners-10 pa">
                @foreach(session()->get('successes')->all() as $message)
                    <p class="body text-white">{{ $message }}</p>
                @endforeach
            </div>
        </div>
    @endif

    @if(session()->has('success-message'))
        <div class="form-success-message container mt-3">
            <div class="flex flex-column bg-success shadow corners-10 pa">
                <p class="body text-white">{{ session()->get('success-message') }}</p>
            </div>
        </div>
    @endif

    @if(session()->has('error-message'))
        <div class="form-success-message container mt-3">
            <div class="flex flex-column bg-error shadow corners-10 pa">
                <p class="body text-white"><strong>{{ session()->get('error-message') }}</strong></p>
            </div>
        </div>
    @endif

    <div class="container mv-3">
        <div class="flex flex-row">
            <div class="flex flex-column grow">
                <div class="flex flex-row bb-grey-1-1">
                    @foreach($sections as $index => $section)
                        <a href="{{ $section['url'] }}" class="flex flex-row align-center body pb-2 ph-1 mr-2 no-decoration text-black
                           {{ $section['active'] ? 'bb-' . $brand . '-3 font-bold' : '' }}">
                            <i class="{{ $section['icon'] }} {{ $section['active'] ? ('text-' . $brand) : '' }}"></i>
                            <span class="hide-xs-only">&nbsp; {{ $section['title'] }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="flex flex-row">
                    <div class="flex flex-column grow">
                        @yield('edit-forms')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

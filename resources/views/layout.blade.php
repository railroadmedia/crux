@php
    $bodyClass = ($bodyClass ?? '') . ' sidebar';
    $leftSidebar = true;
@endphp

@extends('members.layout')

@section('content')
    @include('members.partials._drumeo-sidebar')

    @include('bladesora::members.partials._account-header', [
        "backgroundImage" => 'https://dmmior4id2ysr.cloudfront.net/assets/images/drumeo-members-header-background-image.jpg',
        "userAvatar" => current_user()->getProfilePictureUrl(),
        "userName" => current_user()->getDisplayName(),
        "appName" => 'Drumeo',
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
        <div class="flex flex-row mt-4">
            <div class="flex flex-column grow">
                <div class="flex flex-row bb-grey-1-1">
                    @foreach($sections as $index => $section)
                        <a href="{{ $section['url'] }}" class="flex flex-row tw-justify-start align-center body pb-2 ph-1 mr-2 no-decoration text-black
                           {{ $section['active'] ? 'bb-drumeo-3 font-bold' : '' }}">
                            <i class="{{ $section['icon'] }} {{ $section['active'] ? 'text-drumeo' : '' }}"></i>
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

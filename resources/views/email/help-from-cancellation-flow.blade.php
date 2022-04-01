@extends('crux::email.layout')

@section('page-body')
    <div>
        <p align="center">
            <img src="{{ $brandLogo }}" alt="brand logo" height="50" width="200">
        </p>

        <hr width="100%" color="dddddd">

        <h1 style="font-size:1.2em">"How can we help?"</h1>

        <p><span style="font-style:italic">{{ $userDisplayName }}</span> (<a
                    href="https://musora.com/admin#/users/{{ $userId }}">{{ $userEmail }}</a>) has submitted this
            request for help to get the most from their account:</p>

        <pre style="background:#eeeeee; padding: 10px;">{{ $textInput }}</pre>

        @if($optionDescription)
            <p>Reason selected: "<span style="font-style:italic">{{ $optionDescription }}</span>"</p>
        @else
            <p>No reason selected from list.</p>
        @endif

        <hr width="100%" color="dddddd">

    </div>
@endsection
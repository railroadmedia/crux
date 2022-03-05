@extends('crux::emails.layout')

@section('page-body')
    <div>
        <p align="center">
            <img src="{{ $brandLogo }}"
                 alt="brand logo" height="50" width="200">
        </p>

        <hr width="100%" color="dddddd">

        <h1 style="font-size:1.2em">"How can we make your {{ ucfirst($brand) }} experience better?"</h1>

        <p><span style="font-style:italic">{{ $userDisplayName }}</span> (<a style="color:#8da5ce" href="https://musora.com/admin#/users/{{ $userId }}">{{ $userEmail }}</a>) replied:</p>

        <pre style="background:#eeeeee; padding: 10px;">{{ $userFeedback }}</pre>

        <hr width="100%" color="dddddd">

    </div>
@endsection
@extends('crux::email.layout')

@section('page-body')

    <? /** @var $user \Railroad\Usora\Entities\User */ ?>

    <div>

        <p align="center">
            <img src="{{ $brandLogo }}" alt="brand logo" height="50" width="200">
        </p>

        <h1 style="font-weight:bold;font-size:1.3em;">{{ $subject }}</h1>

        <hr width="100%" color="dddddd">

        <p><span style="font-weight:bold">Reason option selected:</span> "{{ $selection }}"</p>
        <p><span style="font-weight:bold">Reason details:</span></p>
        <pre style="margin: 5px 20px;padding: 10px;background:#f4f4f4;border:1px solid #ddd;">{{ $textInput ?? '' }}</pre>

        <h2 style="font-size:1.2em">General information about user:</h2>
        <ul>
            <li><span style="font-weight:bold">email:</span> {{ $user->getEmail() }}</li>
            <li><span style="font-weight:bold">MusoraCenter page:</span> <a
                        href="https://musora.com/admin#/users/{{ $user->getId() }}">https://musora.com/admin#/users/{{ $user->getId() }}</a>
            </li>

            @foreach($detailedUserInfo['userInfo'] as $key => $value)
                <li><span style="font-weight:bold">{{ $key }}:</span> {{$value ?? '[MISSING]'}}</li>
            @endforeach
        </ul>

        <h2 style="font-size:1.2em">Information about cancelled subscription:</h2>
        <ul>
            @foreach($detailedUserInfo['subscriptionInfo'] as $key => $value)
                <li><span style="font-weight:bold">{{ $key }}:</span> {{
                $value ?? '[MISSING]'
            }}</li>
            @endforeach
        </ul>

        <hr width="100%" color="dddddd">

    </div>

@endsection

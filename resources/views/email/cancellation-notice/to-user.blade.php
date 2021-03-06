@extends('crux::email.layout')

@section('page-body')

<p>We are sorry to see you go! Your {{ ucfirst($brand) }} membership has been deactivated, and your account will not
    auto-renew. We would love to hear your feedback and how {{ ucfirst($brand) }} can continue to support you with your
    musical education.</p>

<p>- The {{ ucfirst($brand) }} Team</p>

@endsection
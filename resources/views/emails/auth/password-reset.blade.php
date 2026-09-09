@php
    use App\Services\SettingsService;

    $appName = $appName ?? SettingsService::get('site_name', 'Banglay Chinese');
@endphp
@extends('emails.layouts.branded')

@section('title')
    Reset your password | {{ $appName }}
@endsection

@section('content')
    <h1 style="margin:0 0 16px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;color:#1E293B;line-height:1.4;">
        Reset your password
    </h1>

    @if ($name)
        <p style="margin:0 0 16px;">Hi {{ $name }},</p>
    @endif

    <p style="margin:0 0 16px;">
        You are receiving this email because we received a password reset request for your {{ $appName }} account.
        Click the button below to choose a new password.
    </p>

    @include('emails.partials.pill-button', ['url' => $url, 'label' => 'Reset Password'])

    <p style="margin:0 0 16px;font-size:13px;color:#64748B;">
        If the button does not work, copy and paste this link into your browser:
    </p>
    <p style="margin:0 0 16px;font-size:13px;word-break:break-all;">
        <a href="{{ $url }}" style="color:#007A3D;text-decoration:underline;">{{ $url }}</a>
    </p>
    <p style="margin:0;font-size:13px;color:#64748B;">
        This password reset link will expire in {{ $expireMinutes }} minutes.
        If you did not request a password reset, no further action is required.
    </p>
@endsection

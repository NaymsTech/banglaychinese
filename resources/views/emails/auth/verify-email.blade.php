@php
    use App\Services\SettingsService;

    $appName = $appName ?? SettingsService::get('site_name', 'Banglay Chinese');
@endphp
@extends('emails.layouts.branded')

@section('title')
    Verify your email | {{ $appName }}
@endsection

@section('content')
    <h1 style="margin:0 0 16px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;color:#1E293B;line-height:1.4;">
        Verify your email
    </h1>

    @if ($name)
        <p style="margin:0 0 16px;">Ni hao, {{ $name }}! 🇨🇳</p>
    @endif

    <p style="margin:0 0 16px;">
        Thanks for creating your {{ $appName }} account. Please confirm your email address by clicking the
        button below — it unlocks your dashboard, courses and downloads.
    </p>

    @include('emails.partials.pill-button', ['url' => $url, 'label' => 'Verify Email Address'])

    <p style="margin:0 0 16px;font-size:13px;color:#64748B;">
        If the button does not work, copy and paste this link into your browser:
    </p>
    <p style="margin:0 0 16px;font-size:13px;word-break:break-all;">
        <a href="{{ $url }}" style="color:#007A3D;text-decoration:underline;">{{ $url }}</a>
    </p>
    <p style="margin:0;font-size:13px;color:#64748B;">
        This verification link will expire in {{ $expireMinutes }} minutes. If you did not create an
        account, you can safely ignore this email.
    </p>
@endsection

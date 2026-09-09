{{--
    Banglay Chinese — branded transactional email shell.

    One reusable presentation layer for every email the application sends:
      - database template bodies (raw HTML content with {placeholders} already
        resolved) are wrapped via App\Support\EmailShell::render()
      - Blade fallbacks (e.g. the auth verify/reset views) can extend this
        layout and fill the `content` section instead.

    Email-safe rules used throughout: table-based structure, fully inline
    styles, ~600px fluid container, no external fonts or assets, and absolute
    image/link URLs. The canonical pill button for use inside template bodies
    lives in resources/views/emails/partials/pill-button.blade.php.

    Design variables are NOT hardcoded here. They are resolved from the
    centralized EmailBranding configuration by EmailShell::brandingVariables()
    and can be overridden per render:

    $content | $siteName | $tagline | $websiteUrl | $logoUrl
    $primaryColor | $accentColor | $bodyTextColor | $mutedTextColor
    $footerText | $contactEmail | $contactPhone | $copyright
    $whatsappUrl | $facebookUrl | $instagramUrl | $youtubeUrl

    $primaryColor drives the header tagline and the top accent strip;
    $accentColor is the deep footer band behind the contact/social links;
    $bodyTextColor and $mutedTextColor color the message and the quiet notes.
    Social values are empty strings when not configured — the footer then
    renders no broken link for that network.
--}}
@php
    use App\Support\EmailShell;

    $branding = EmailShell::brandingVariables();

    $siteName = $siteName ?? $branding['siteName'];
    $tagline = $tagline ?? $branding['tagline'];
    $websiteUrl = $websiteUrl ?? $branding['websiteUrl'];
    $logoUrl = $logoUrl ?? $branding['logoUrl'];
    $primaryColor = $primaryColor ?? $branding['primaryColor'];
    $accentColor = $accentColor ?? $branding['accentColor'];
    $bodyTextColor = $bodyTextColor ?? $branding['bodyTextColor'];
    $mutedTextColor = $mutedTextColor ?? $branding['mutedTextColor'];
    $footerText = $footerText ?? $branding['footerText'];
    $contactEmail = $contactEmail ?? $branding['contactEmail'];
    $contactPhone = $contactPhone ?? $branding['contactPhone'];
    $whatsappUrl = $whatsappUrl ?? $branding['whatsappUrl'];
    $facebookUrl = $facebookUrl ?? $branding['facebookUrl'];
    $instagramUrl = $instagramUrl ?? $branding['instagramUrl'];
    $youtubeUrl = $youtubeUrl ?? $branding['youtubeUrl'];
    $copyright = $copyright ?? $branding['copyright'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@hasSection('title')@yield('title')@else{{ $title ?? $siteName }}@endif</title>
    <style>
        #outlook a { padding: 0; }
    </style>
    <!--[if mso]>
    <style type="text/css">
        table { border-collapse: collapse; }
        td, a { font-family: 'Segoe UI', Arial, sans-serif; }
        img { -ms-interpolation-mode: bicubic; }
    </style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#F4F7F5;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;word-spacing:normal;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F4F7F5;">
        <tr>
            <td align="center" style="padding:32px 16px 40px;">
                {{-- Fluid container, ~600px on desktop --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">

                    {{-- Header: logo + tagline --}}
                    <tr>
                        <td align="center" style="padding:0 12px 24px;">
                            <a href="{{ $websiteUrl }}" target="_blank" style="text-decoration:none;border:0;outline:none;">
                                <img
                                    src="{{ $logoUrl }}"
                                    alt="{{ $siteName }} — Learn Chinese in Bangla"
                                    width="180"
                                    style="display:block;width:180px;max-width:100%;height:auto;border:0;outline:none;text-decoration:none;"
                                >
                            </a>
                            <p style="margin:10px 0 0;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.4px;color:{{ $primaryColor }};line-height:1.5;">
                                {{ $tagline }}
                            </p>
                        </td>
                    </tr>

                    {{-- White card --}}
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;box-shadow:0 6px 24px rgba(16,24,40,0.06);">

                                {{-- Brand accent strip --}}
                                <tr>
                                    <td height="6" style="height:6px;font-size:0;line-height:0;background-color:{{ $primaryColor }};border-radius:16px 16px 0 0;">&nbsp;</td>
                                </tr>

                                {{-- Main content: template body goes here --}}
                                <tr>
                                    <td style="padding:36px 36px 32px;font-family:'Hind Siliguri','Inter',Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;color:{{ $bodyTextColor }};">
                                        @hasSection('content')
                                            @yield('content')
                                        @else
                                            {!! $content ?? '' !!}
                                        @endif
                                    </td>
                                </tr>

                                {{-- Compact branded footer --}}
                                <tr>
                                    <td align="center" style="background-color:{{ $accentColor }};padding:28px 36px 24px;border-radius:0 0 16px 16px;">
                                        <p style="margin:0;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:16px;font-weight:700;color:#FFFFFF;line-height:1.4;">
                                            {{ $siteName }}
                                        </p>
                                        <p style="margin:4px 0 16px;font-family:'Hind Siliguri','Inter',Arial,sans-serif;font-size:11px;line-height:1.6;color:#E6F4EA;">
                                            {{ $tagline }}
                                        </p>

                                        @php
                                            $footerLinks = [
                                                ['label' => 'Website', 'url' => $websiteUrl],
                                            ];
                                            foreach ([
                                                ['label' => 'WhatsApp', 'url' => $whatsappUrl],
                                                ['label' => 'Facebook', 'url' => $facebookUrl],
                                                ['label' => 'Instagram', 'url' => $instagramUrl],
                                                ['label' => 'YouTube', 'url' => $youtubeUrl],
                                            ] as $social) {
                                                if (filled($social['url'])) {
                                                    $footerLinks[] = $social;
                                                }
                                            }
                                        @endphp
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid rgba(230,244,234,0.25);">
                                            <tr>
                                                <td align="center" style="padding:14px 0 0;font-family:'Hind Siliguri','Inter',Arial,sans-serif;font-size:12px;line-height:1.8;">
                                                    @foreach ($footerLinks as $footerLinkIndex => $footerLink)
                                                        @if ($footerLinkIndex > 0)
                                                            <span style="color:rgba(230,244,234,0.6);padding:0 6px;">•</span>
                                                        @endif
                                                        <a href="{{ $footerLink['url'] }}" target="_blank" style="color:#E6F4EA;text-decoration:underline;">{{ $footerLink['label'] }}</a>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        </table>

                                        @if (filled($footerText))
                                            <p style="margin:14px 0 0;font-family:'Hind Siliguri','Inter',Arial,sans-serif;font-size:12px;line-height:1.6;color:#E6F4EA;">
                                                {{ $footerText }}
                                            </p>
                                        @endif

                                        <p style="margin:14px 0 0;font-family:'Hind Siliguri','Inter',Arial,sans-serif;font-size:11px;line-height:1.6;color:#E6F4EA;opacity:0.8;">
                                            {{ $copyright }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Quiet post-script row --}}
                    <tr>
                        <td align="center" style="padding:16px 12px 0;font-family:'Hind Siliguri','Inter',Arial,sans-serif;font-size:11px;line-height:1.6;color:{{ $mutedTextColor }};">
                            Need help? Reply to this email or reach us at
                            <a href="mailto:{{ $contactEmail }}" style="color:{{ $mutedTextColor }};text-decoration:underline;">{{ $contactEmail }}</a>
                            @if (filled($contactPhone))
                                <span style="color:{{ $mutedTextColor }};padding:0 6px;">•</span>
                                <span style="color:{{ $mutedTextColor }};">{{ $contactPhone }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

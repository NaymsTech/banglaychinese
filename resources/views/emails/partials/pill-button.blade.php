{{--
    Banglay Chinese — pill-shaped CTA for emails.

    @include('emails.partials.pill-button', ['url' => ..., 'label' => ...])

    This markup is also the canonical pill button for database template bodies
    (raw HTML is inserted into the shell unchanged, so template authors should
    copy this snippet rather than using @include). Older clients that cannot
    render border-radius fall back to a square button, which is acceptable.
--}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;"
                        >
                            {{ $label }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

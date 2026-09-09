<?php

namespace App\Support;

/**
 * Snapshot of the pre-branded (legacy) template bodies that older databases
 * may still hold.
 *
 * These strings exist only so the `email:sync-branded-templates` command can
 * tell "still the old default" apart from "customized by an administrator".
 * A row is only ever rewritten when its stored body matches one of these known
 * legacy defaults (comparison is whitespace-insensitive); anything else is
 * treated as customized and skipped. Bodies are intentionally NOT duplicated
 * for the current branded content — the canonical definitions live in
 * Database\Seeders\EmailSystemSeeder::definitions().
 */
class EmailTemplateLegacyBodies
{
    /**
     * The known old default body for every production template key.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'product_approved' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">Your download is ready, {student_name}!</h2>
    <p>Great news — your payment was approved and your copy of <strong>{product_title}</strong> is ready to download.</p>
    <p style="text-align: center; margin: 32px 0;">
        <a href="{download_link}" style="background-color: #0f5132; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold;">
            Download Now
        </a>
    </p>
    <p>If the button does not work, copy and paste this link into your browser:</p>
    <p><a href="{download_link}" style="color: #0f5132;">{download_link}</a></p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Thank you for learning with Banglay Chinese!<br>If you need help, reply to this email or message us on WhatsApp.</p>
</div>
HTML,
            'payment_reminder' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">Friendly payment reminder</h2>
    <p>Hi {student_name},</p>
    <p>This is a gentle reminder that <strong>{amount_due}</strong> is still due for <strong>{course_title}</strong>. Please complete your payment by <strong>{due_date}</strong> so we can activate your enrollment.</p>
    <p>You can pay via <strong>bKash</strong> to <strong>{bkash_number}</strong> or via <strong>Nagad</strong> to <strong>{nagad_number}</strong>, then send us the transaction ID so we can confirm your spot.</p>
    <p>Questions? WhatsApp us at <strong>{whatsapp_number}</strong> or email <strong>{contact_email}</strong> — we reply within 24 hours.</p>
</div>
HTML,
            'course_enrollment_confirmation' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">Welcome aboard, {student_name}! 🎓</h2>
    <p>Your enrollment in <strong>{course_title}</strong> is confirmed. Here is what happens next:</p>
    <ul>
        <li>Log in to your account and open <strong>Dashboard → My Courses</strong>.</li>
        <li>Work through the modules at your own pace — each lesson tracks your progress.</li>
        <li>Stuck on something? Reply to this email and we will help.</li>
    </ul>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Banglay Chinese — learn Chinese the smart way, in Bangla.</p>
</div>
HTML,
            'welcome_email' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">Ni hao, {student_name}! 🇨🇳</h2>
    <p>Welcome to Banglay Chinese — the platform where Bangladeshi students learn Mandarin and prepare to study in China, explained in Bangla.</p>
    <p>Here is what you can expect from us:</p>
    <ul>
        <li>HSK 1–4 courses with bite-sized lessons and progress tracking.</li>
        <li>Free resources, vocabulary lists and speaking practice.</li>
        <li>Personal guidance for scholarships and university applications in China.</li>
    </ul>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Start exploring our free resources today — 加油 (jiā yóu)!</p>
</div>
HTML,
            'application_received' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">Application received, {student_name}! 📋</h2>
    <p>Thank you for applying for <strong>{desired_program}</strong>. Our team has received your application and will review it shortly.</p>
    <p><strong>What happens next:</strong></p>
    <ul>
        <li>Our consultants will contact you within 1–2 business days.</li>
        <li>We will guide you through document preparation and deadlines.</li>
        <li>You will hear the final outcome as soon as the university responds.</li>
    </ul>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Questions in the meantime? Reply to this email — we are happy to help.</p>
</div>
HTML,
            'contact_inquiry_received' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">Thank you for reaching out, {student_name}! ✉️</h2>
    <p>We have received your message and will get back to you <strong>within 24 hours</strong>.</p>
    <p>If your question is urgent, you can also reach us instantly on WhatsApp — the link is on our website.</p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Banglay Chinese — your bridge to China.</p>
</div>
HTML,
            'email_verification' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify your email | {appName}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f7f5;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7f5;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#0f5132;padding:28px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:bold;">Verify your email</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;color:#1f2937;font-size:15px;line-height:1.6;">
                            <p style="margin:0 0 16px;">Ni hao, {name}! 🇨🇳</p>
                            <p style="margin:0 0 16px;">
                                Thanks for creating your {appName} account. Please confirm your email address by clicking the
                                button below — it unlocks your dashboard, courses and downloads.
                            </p>
                            <p style="text-align:center;margin:28px 0;">
                                <a href="{url}" style="background-color:#0f5132;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block;">Verify Email Address</a>
                            </p>
                            <p style="margin:0 0 16px;font-size:13px;color:#6b7280;">
                                If the button does not work, copy and paste this link into your browser:
                            </p>
                            <p style="margin:0 0 16px;font-size:13px;word-break:break-all;">
                                <a href="{url}" style="color:#0f5132;">{url}</a>
                            </p>
                            <p style="margin:0;font-size:13px;color:#6b7280;">
                                This verification link will expire in {expireMinutes} minutes. If you did not create an
                                account, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9fafb;padding:20px 32px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:13px;color:#6b7280;">{appName} — learn Chinese the smart way, in Bangla. 加油!</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML,
            'password_reset' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your password | {appName}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f7f5;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7f5;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#0f5132;padding:28px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:bold;">Reset your password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;color:#1f2937;font-size:15px;line-height:1.6;">
                            <p style="margin:0 0 16px;">Hi {name},</p>
                            <p style="margin:0 0 16px;">
                                You are receiving this email because we received a password reset request for your {appName} account.
                                Click the button below to choose a new password.
                            </p>
                            <p style="text-align:center;margin:28px 0;">
                                <a href="{url}" style="background-color:#0f5132;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block;">Reset Password</a>
                            </p>
                            <p style="margin:0 0 16px;font-size:13px;color:#6b7280;">
                                If the button does not work, copy and paste this link into your browser:
                            </p>
                            <p style="margin:0 0 16px;font-size:13px;word-break:break-all;">
                                <a href="{url}" style="color:#0f5132;">{url}</a>
                            </p>
                            <p style="margin:0;font-size:13px;color:#6b7280;">
                                This password reset link will expire in {expireMinutes} minutes.
                                If you did not request a password reset, no further action is required.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9fafb;padding:20px 32px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:13px;color:#6b7280;">{appName} — learn Chinese the smart way, in Bangla.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML,
            'order_received_payment_pending' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #0f5132;">আপনার অর্ডার আমরা পেয়েছি! 🎉</h2>
    <p>প্রিয় {student_name},</p>
    <p>আপনার অর্ডারটি সফলভাবে জমা হয়েছে:</p>
    <ul>
        <li><strong>অর্ডার নম্বর:</strong> #{order_number}</li>
        <li><strong>আইটেম:</strong> {product_title}</li>
        <li><strong>মোট:</strong> {amount}</li>
    </ul>
    <p>আপনার পেমেন্টটি বর্তমানে <strong>ম্যানুয়াল যাচাইকরণের</strong> অধীনে রয়েছে। যাচাই সম্পন্ন হওয়ার আগ পর্যন্ত কোর্স/ডাউনলোড অ্যাক্সেস সক্রিয় হবে না।</p>
    <p>অ্যাডমিন পেমেন্ট যাচাই করে অনুমোদন করলে আপনি আলাদা একটি ইমেইলে অ্যাক্সেসের তথ্য পাবেন।</p>
    <p style="text-align: center; margin: 32px 0;">
        <a href="{order_url}" style="background-color: #0f5132; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">অর্ডারের অবস্থা দেখুন</a>
    </p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">প্রশ্ন থাকলে রিপ্লাই করুন — আমরা ২৪ ঘণ্টার মধ্যে উত্তর দেব।</p>
</div>
HTML,
            'payment_verification_failed' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #b91c1c;">পেমেন্ট যাচাই সফল হয়নি</h2>
    <p>প্রিয় {student_name},</p>
    <p>দুঃখিত, আমরা <strong>#{order_number}</strong> (আইটেম: {product_title}, মোট: {amount}) অর্ডারটির পেমেন্ট যাচাই করতে পারিনি।</p>
    <p><strong>কারণ:</strong> {rejection_reason}</p>
    <p>এ কারণে অর্ডারটি অনুমোদন করা হয়নি এবং কোর্স/ডাউনলোড অ্যাক্সেস দেওয়া হয়নি। সঠিক তথ্য দিয়ে আপনি নতুন করে আবেদন করতে পারেন অথবা আমাদের সাথে যোগাযোগ করুন।</p>
    <p style="text-align: center; margin: 32px 0;">
        <a href="{support_url}" style="background-color: #0f5132; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">সহায়তা নিন</a>
    </p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Banglay Chinese — আপনার প্রশ্নের উত্তর দিতে আমরা সবসময় প্রস্তুত।</p>
</div>
HTML,
            'payment_information_needs_attention' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; color: #1f2937;">
    <h2 style="color: #b45309;">পেমেন্টের তথ্য আপডেট প্রয়োজন</h2>
    <p>প্রিয় {student_name},</p>
    <p><strong>#{order_number}</strong> (আইটেম: {product_title}) অর্ডারটির পেমেন্ট যাচাই করতে আমাদের কিছু অতিরিক্ত তথ্য দরকার:</p>
    <p style="background: #fef3c7; padding: 12px 16px; border-radius: 6px;"><strong>{required_action}</strong></p>
    <p>অনুগ্রহ করে নিচের বাটনে ক্লিক করে সঠিক তথ্য দিয়ে আবার জমা দিন। আপনার তথ্য পাওয়ার পর আমরা পুনরায় যাচাই করব।</p>
    <p style="text-align: center; margin: 32px 0;">
        <a href="{order_url}" style="background-color: #0f5132; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">তথ্য আপডেট করুন</a>
    </p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <p style="font-size: 13px; color: #6b7280;">Banglay Chinese — learn Chinese the smart way, in Bangla.</p>
</div>
HTML,
        ];
    }

    /**
     * The known old default body for one template key, when it exists.
     */
    public static function body(string $key): ?string
    {
        return self::all()[$key] ?? null;
    }
}

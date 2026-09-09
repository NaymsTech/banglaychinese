<?php

namespace Database\Seeders;

use App\Models\EmailBranding;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Centralized email branding (logo/colours/tagline/footer) is seeded
        // with its built-in defaults. firstOrCreate means administrator edits
        // are never overwritten by a re-seed — exactly like the templates.
        EmailBranding::ensureExists();

        EmailProvider::firstOrCreate(
            ['name' => 'Brevo SMTP'],
            [
                'driver' => EmailProvider::DRIVER_SMTP,
                'config' => [
                    'host' => env('MAIL_HOST', 'smtp-relay.brevo.com'),
                    'port' => (int) env('MAIL_PORT', 587),
                    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                    'username' => env('MAIL_USERNAME', ''),
                    'password' => env('MAIL_PASSWORD', ''),
                    'from_address' => env('MAIL_FROM_ADDRESS', 'info@banglaychinese.com'),
                    'from_name' => env('MAIL_FROM_NAME', 'Banglay Chinese'),
                ],
                'is_active' => true,
                'priority' => 1,
                'daily_limit' => 300,
                'sent_today' => 0,
                'last_reset' => today(),
            ]
        );

        // Default template content is defined once in definitions(), shared by
        // this seeder and the `email:sync-branded-templates` console command.
        foreach (static::definitions() as $template) {
            // Only create missing templates — administrator edits to existing
            // rows are never overwritten by a re-seed; the description
            // backfill below touches only rows whose description is still NULL.
            EmailTemplate::firstOrCreate(
                ['key' => $template['key']],
                $template
            );
        }

        // Backfill the (previously non-existent) description on legacy rows
        // without touching any other column an admin may have edited.
        foreach (static::definitions() as $template) {
            EmailTemplate::query()
                ->where('key', $template['key'])
                ->whereNull('description')
                ->update(['description' => $template['description']]);
        }
    }

    /**
     * Canonical definitions for the 11 production email templates.
     *
     * Single source of truth consumed by fresh installs (this seeder) and by
     * the `email:sync-branded-templates` command that brings existing
     * databases up to the same branded bodies. run() above only creates
     * missing rows; the sync command updates only rows whose stored body still
     * matches a known legacy default (see App\Support\EmailTemplateLegacyBodies).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            [
                'name' => 'Product Download Ready',
                'key' => 'product_approved',
                'description' => 'Sent when a digital order is approved, with the download link.',
                'from_address' => 'no-reply@banglaychinese.com',
                'from_name' => 'Banglay Chinese Downloads',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'Your Download is Ready! 🎉',
                'variables' => ['student_name', 'product_title', 'download_link'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Your download is ready, {student_name}! 🎉</h2>
<p style="margin:0 0 16px;line-height:1.7;">Great news — your payment was approved and your copy of <strong>{product_title}</strong> is ready to download.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a href="{download_link}" target="_blank" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">Download Now</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p style="margin:0 0 6px;font-size:13px;color:#64748B;line-height:1.6;">If the button does not work, copy and paste this link into your browser:</p>
<p style="margin:0 0 20px;font-size:13px;word-break:break-all;"><a href="{download_link}" style="color:#007A3D;text-decoration:underline;">{download_link}</a></p>
<hr style="border:none;border-top:1px solid #E2E8F0;margin:28px 0;">
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Thank you for learning with Banglay Chinese!<br>If you need help, reply to this email or message us on WhatsApp.</p>
HTML,
            ],
            [
                'name' => 'Payment Reminder',
                'key' => 'payment_reminder',
                'description' => 'Friendly dunning email for enrollments with an outstanding balance.',
                'category' => EmailTemplate::CATEGORY_REMINDER,
                'subject' => 'Payment Reminder: {course_title}',
                'variables' => ['student_name', 'course_title', 'amount_due', 'due_date'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Friendly payment reminder</h2>
<p style="margin:0 0 16px;line-height:1.7;">Hi {student_name},</p>
<p style="margin:0 0 16px;line-height:1.7;">This is a gentle reminder that <strong>{amount_due}</strong> is still due for <strong>{course_title}</strong>. Please complete your payment by <strong>{due_date}</strong> so we can activate your enrollment.</p>
<p style="margin:0 0 10px;line-height:1.7;">You can pay using either method below, then send us the transaction ID so we can confirm your spot:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
    <tr>
        <td style="background-color:#E6F4EA;border-left:4px solid #007A3D;border-radius:8px;padding:14px 18px;font-size:14px;line-height:1.8;color:#004D26;">
            <strong>bKash:</strong> {bkash_number}<br>
            <strong>Nagad:</strong> {nagad_number}
        </td>
    </tr>
</table>
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Questions? WhatsApp us at <strong style="color:#1E293B;">{whatsapp_number}</strong> or email <a href="mailto:{contact_email}" style="color:#007A3D;text-decoration:underline;">{contact_email}</a> — we reply within 24 hours.</p>
HTML,
            ],
            [
                'name' => 'Course Enrollment Confirmation',
                'key' => 'course_enrollment_confirmation',
                'description' => 'Sent when an enrollment payment is marked as paid.',
                'from_address' => 'no-reply@banglaychinese.com',
                'from_name' => 'Banglay Chinese Courses',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'Welcome to {course_title}! 🎓',
                'variables' => ['student_name', 'course_title'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Welcome aboard, {student_name}! 🎓</h2>
<p style="margin:0 0 16px;line-height:1.7;">Your enrollment in <strong>{course_title}</strong> is confirmed. Here is what happens next:</p>
<ul style="margin:0 0 20px;padding-left:22px;">
    <li style="margin:0 0 8px;line-height:1.7;">Log in to your account and open <strong>Dashboard → My Courses</strong>.</li>
    <li style="margin:0 0 8px;line-height:1.7;">Work through the modules at your own pace — each lesson tracks your progress.</li>
    <li style="margin:0;line-height:1.7;">Stuck on something? Reply to this email and we will help.</li>
</ul>
<hr style="border:none;border-top:1px solid #E2E8F0;margin:28px 0;">
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Banglay Chinese — learn Chinese the smart way, in Bangla.</p>
HTML,
            ],
            [
                'name' => 'Welcome to Banglay Chinese',
                'key' => 'welcome_email',
                'description' => 'One-time welcome sent after a new user verifies their email.',
                'from_address' => 'info@banglaychinese.com',
                'from_name' => 'Banglay Chinese',
                'category' => EmailTemplate::CATEGORY_NOTIFICATION,
                'subject' => 'Welcome to Banglay Chinese! 🇨🇳',
                'variables' => ['student_name'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Ni hao, {student_name}! 🇨🇳</h2>
<p style="margin:0 0 16px;line-height:1.7;">Welcome to Banglay Chinese — the platform where Bangladeshi students learn Mandarin and prepare to study in China, explained in Bangla.</p>
<p style="margin:0 0 10px;line-height:1.7;">Here is what you can expect from us:</p>
<ul style="margin:0 0 20px;padding-left:22px;">
    <li style="margin:0 0 8px;line-height:1.7;">HSK 1–4 courses with bite-sized lessons and progress tracking.</li>
    <li style="margin:0 0 8px;line-height:1.7;">Free resources, vocabulary lists and speaking practice.</li>
    <li style="margin:0;line-height:1.7;">Personal guidance for scholarships and university applications in China.</li>
</ul>
<hr style="border:none;border-top:1px solid #E2E8F0;margin:28px 0;">
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Start exploring our free resources today — 加油 (jiā yóu)!</p>
HTML,
            ],
            [
                'name' => 'Application Received',
                'key' => 'application_received',
                'description' => 'Acknowledgement for a study-in-China scholarship application.',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'We Received Your Application! 📋',
                'variables' => ['student_name', 'desired_program'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Application received, {student_name}! 📋</h2>
<p style="margin:0 0 16px;line-height:1.7;">Thank you for applying for <strong>{desired_program}</strong>. Our team has received your application and will review it shortly.</p>
<p style="margin:0 0 10px;line-height:1.7;"><strong>What happens next:</strong></p>
<ul style="margin:0 0 20px;padding-left:22px;">
    <li style="margin:0 0 8px;line-height:1.7;">Our consultants will contact you within 1–2 business days.</li>
    <li style="margin:0 0 8px;line-height:1.7;">We will guide you through document preparation and deadlines.</li>
    <li style="margin:0;line-height:1.7;">You will hear the final outcome as soon as the university responds.</li>
</ul>
<hr style="border:none;border-top:1px solid #E2E8F0;margin:28px 0;">
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Questions in the meantime? Reply to this email — we are happy to help.</p>
HTML,
            ],
            [
                'name' => 'Contact Inquiry Received',
                'key' => 'contact_inquiry_received',
                'description' => 'Acknowledgement sent after the public contact form is submitted.',
                'from_address' => 'support@banglaychinese.com',
                'from_name' => 'Banglay Chinese Support',
                'category' => EmailTemplate::CATEGORY_NOTIFICATION,
                'subject' => 'We Received Your Message! ✉️',
                'variables' => ['student_name'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Thank you for reaching out, {student_name}! ✉️</h2>
<p style="margin:0 0 16px;line-height:1.7;">We have received your message and will get back to you <strong>within 24 hours</strong>.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
    <tr>
        <td style="background-color:#E6F4EA;border-left:4px solid #007A3D;border-radius:8px;padding:14px 18px;font-size:14px;line-height:1.7;color:#004D26;">
            If your question is urgent, you can also reach us instantly on WhatsApp — the link is on our website.
        </td>
    </tr>
</table>
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Banglay Chinese — your bridge to China.</p>
HTML,
            ],
            [
                'name' => 'Email Verification',
                'key' => 'email_verification',
                'description' => 'System-critical: sent to verify a new or changed email address.',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'Verify your {appName} email',
                'variables' => ['name', 'appName', 'url', 'expireMinutes'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Verify your email</h2>
<p style="margin:0 0 16px;line-height:1.7;">Ni hao, {name}! 🇨🇳</p>
<p style="margin:0 0 16px;line-height:1.7;">Thanks for creating your {appName} account. Please confirm your email address by clicking the button below — it unlocks your dashboard, courses and downloads.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a href="{url}" target="_blank" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">Verify Email Address</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p style="margin:0 0 6px;font-size:13px;color:#64748B;line-height:1.6;">If the button does not work, copy and paste this link into your browser:</p>
<p style="margin:0;font-size:13px;word-break:break-all;"><a href="{url}" style="color:#007A3D;text-decoration:underline;">{url}</a></p>
<p style="margin:16px 0 0;font-size:13px;color:#64748B;line-height:1.6;">This verification link will expire in {expireMinutes} minutes. If you did not create an account, you can safely ignore this email.</p>
HTML,
            ],
            [
                'name' => 'Password Reset',
                'key' => 'password_reset',
                'description' => 'System-critical: sent with the secure link to choose a new password.',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'Reset your {appName} password',
                'variables' => ['name', 'appName', 'url', 'expireMinutes'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:#007A3D;">Reset your password</h2>
<p style="margin:0 0 16px;line-height:1.7;">Hi {name},</p>
<p style="margin:0 0 16px;line-height:1.7;">You are receiving this email because we received a password reset request for your {appName} account. Click the button below to choose a new password.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a href="{url}" target="_blank" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Poppins','Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">Reset Password</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p style="margin:0 0 6px;font-size:13px;color:#64748B;line-height:1.6;">If the button does not work, copy and paste this link into your browser:</p>
<p style="margin:0;font-size:13px;word-break:break-all;"><a href="{url}" style="color:#007A3D;text-decoration:underline;">{url}</a></p>
<p style="margin:16px 0 0;font-size:13px;color:#64748B;line-height:1.6;">This password reset link will expire in {expireMinutes} minutes. If you did not request a password reset, no further action is required.</p>
HTML,
            ],
            [
                'name' => 'Order Received — Payment Pending',
                'key' => 'order_received_payment_pending',
                'description' => 'Sent right after a course enrollment or digital order is created; payment is awaiting manual verification and access is not yet active.',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'আপনার অর্ডার আমরা পেয়েছি — পেমেন্ট যাচাই চলছে',
                'variables' => ['student_name', 'order_number', 'product_title', 'amount', 'order_url'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Hind Siliguri',Arial,sans-serif;font-size:22px;font-weight:700;line-height:1.5;color:#007A3D;">আপনার অর্ডার আমরা পেয়েছি! 🎉</h2>
<p style="margin:0 0 16px;line-height:1.7;">প্রিয় {student_name},</p>
<p style="margin:0 0 10px;line-height:1.7;">আপনার অর্ডারটি সফলভাবে জমা হয়েছে:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
    <tr>
        <td style="background-color:#E6F4EA;border-left:4px solid #007A3D;border-radius:8px;padding:14px 18px;font-size:14px;line-height:1.9;color:#004D26;">
            <strong>অর্ডার নম্বর:</strong> #{order_number}<br>
            <strong>আইটেম:</strong> {product_title}<br>
            <strong>মোট:</strong> {amount}
        </td>
    </tr>
</table>
<p style="margin:0 0 16px;line-height:1.7;">আপনার পেমেন্টটি বর্তমানে <strong>ম্যানুয়াল যাচাইকরণের</strong> অধীনে রয়েছে। যাচাই সম্পন্ন হওয়ার আগ পর্যন্ত কোর্স/ডাউনলোড অ্যাক্সেস সক্রিয় হবে না।</p>
<p style="margin:0 0 16px;line-height:1.7;">অ্যাডমিন পেমেন্ট যাচাই করে অনুমোদন করলে আপনি আলাদা একটি ইমেইলে অ্যাক্সেসের তথ্য পাবেন।</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a href="{order_url}" target="_blank" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">অর্ডারের অবস্থা দেখুন</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">প্রশ্ন থাকলে রিপ্লাই করুন — আমরা ২৪ ঘণ্টার মধ্যে উত্তর দেব।</p>
HTML,
            ],
            [
                'name' => 'Payment Verification Failed',
                'key' => 'payment_verification_failed',
                'description' => 'Sent when an admin rejects a payment; explains that verification was unsuccessful and shows the reason when provided.',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'আপনার পেমেন্ট যাচাই করা যায়নি — অর্ডার {order_number}',
                'variables' => ['student_name', 'order_number', 'product_title', 'amount', 'rejection_reason', 'support_url'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Hind Siliguri',Arial,sans-serif;font-size:22px;font-weight:700;line-height:1.5;color:#B91C1C;">পেমেন্ট যাচাই সফল হয়নি</h2>
<p style="margin:0 0 16px;line-height:1.7;">প্রিয় {student_name},</p>
<p style="margin:0 0 16px;line-height:1.7;">দুঃখিত, আমরা <strong>#{order_number}</strong> (আইটেম: {product_title}, মোট: {amount}) অর্ডারটির পেমেন্ট যাচাই করতে পারিনি।</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
    <tr>
        <td style="background-color:#FEE2E2;border-left:4px solid #B91C1C;border-radius:8px;padding:14px 18px;font-size:14px;line-height:1.8;color:#7F1D1D;">
            <strong>কারণ:</strong> {rejection_reason}
        </td>
    </tr>
</table>
<p style="margin:0 0 16px;line-height:1.7;">এ কারণে অর্ডারটি অনুমোদন করা হয়নি এবং কোর্স/ডাউনলোড অ্যাক্সেস দেওয়া হয়নি। সঠিক তথ্য দিয়ে আপনি নতুন করে আবেদন করতে পারেন অথবা আমাদের সাথে যোগাযোগ করুন।</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a href="{support_url}" target="_blank" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">সহায়তা নিন</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Banglay Chinese — আপনার প্রশ্নের উত্তর দিতে আমরা সবসময় প্রস্তুত।</p>
HTML,
            ],
            [
                'name' => 'Payment Information Needs Attention',
                'key' => 'payment_information_needs_attention',
                'description' => 'Sent when an admin needs the student to correct payment details before verification can continue.',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'আপনার পেমেন্টের কিছু তথ্য আপডেট করা প্রয়োজন',
                'variables' => ['student_name', 'order_number', 'product_title', 'required_action', 'order_url'],
                'body' => <<<'HTML'
<h2 style="margin:0 0 18px;font-family:'Hind Siliguri',Arial,sans-serif;font-size:22px;font-weight:700;line-height:1.5;color:#B45309;">পেমেন্টের তথ্য আপডেট প্রয়োজন</h2>
<p style="margin:0 0 16px;line-height:1.7;">প্রিয় {student_name},</p>
<p style="margin:0 0 16px;line-height:1.7;"><strong>#{order_number}</strong> (আইটেম: {product_title}) অর্ডারটির পেমেন্ট যাচাই করতে আমাদের কিছু অতিরিক্ত তথ্য দরকার:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
    <tr>
        <td style="background-color:#FEF3C7;border-left:4px solid #F59E0B;border-radius:8px;padding:14px 18px;font-size:14px;line-height:1.8;color:#92400E;">
            <strong>{required_action}</strong>
        </td>
    </tr>
</table>
<p style="margin:0 0 16px;line-height:1.7;">অনুগ্রহ করে নিচের বাটনে ক্লিক করে সঠিক তথ্য দিয়ে আবার জমা দিন। আপনার তথ্য পাওয়ার পর আমরা পুনরায় যাচাই করব।</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" bgcolor="#007A3D" style="border-radius:999px;background-color:#007A3D;">
                        <a href="{order_url}" target="_blank" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:#007A3D;color:#FFFFFF;font-family:'Hind Siliguri',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">তথ্য আপডেট করুন</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p style="margin:0;font-size:13px;color:#64748B;line-height:1.6;">Banglay Chinese — learn Chinese the smart way, in Bangla.</p>
HTML,
            ],
        ];
    }
}

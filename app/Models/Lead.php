<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_CONTACT_FORM = 'contact_form';

    public const SOURCE_CHECKOUT = 'checkout';

    public const SOURCE_APPLICATION = 'application';

    public const SOURCE_REGISTRATION = 'registration';

    public const SOURCE_NEWSLETTER = 'newsletter';

    public const SOURCES = [
        self::SOURCE_WEBSITE => 'Website',
        self::SOURCE_CONTACT_FORM => 'Contact form',
        self::SOURCE_CHECKOUT => 'Checkout',
        self::SOURCE_APPLICATION => 'Application',
        self::SOURCE_REGISTRATION => 'Registration',
        self::SOURCE_NEWSLETTER => 'Newsletter',
    ];

    public const SOURCE_COLORS = [
        self::SOURCE_WEBSITE => 'gray',
        self::SOURCE_CONTACT_FORM => 'info',
        self::SOURCE_CHECKOUT => 'success',
        self::SOURCE_APPLICATION => 'warning',
        self::SOURCE_REGISTRATION => 'purple',
        self::SOURCE_NEWSLETTER => 'sky',
    ];

    public const INTEREST_GENERAL = 'general';

    public const INTEREST_STUDY_IN_CHINA = 'study_in_china';

    public const INTEREST_COURSES = 'courses';

    public const INTEREST_DIGITAL_PRODUCTS = 'digital_products';

    public const INTEREST_MENTORSHIP = 'mentorship';

    public const INTERESTS = [
        self::INTEREST_GENERAL => 'General',
        self::INTEREST_STUDY_IN_CHINA => 'Study in China',
        self::INTEREST_COURSES => 'Courses',
        self::INTEREST_DIGITAL_PRODUCTS => 'Digital products',
        self::INTEREST_MENTORSHIP => 'Mentorship',
    ];

    public const INTEREST_COLORS = [
        self::INTEREST_GENERAL => 'gray',
        self::INTEREST_STUDY_IN_CHINA => 'warning',
        self::INTEREST_COURSES => 'info',
        self::INTEREST_DIGITAL_PRODUCTS => 'success',
        self::INTEREST_MENTORSHIP => 'purple',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'whatsapp_number',
        'source',
        'interest',
        'notes',
        'is_subscribed',
    ];

    protected $casts = [
        'is_subscribed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

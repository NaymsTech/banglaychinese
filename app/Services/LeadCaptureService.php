<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Str;

class LeadCaptureService
{
    /**
     * Store a lead for the given contact details without ever creating
     * duplicates.
     *
     * The first interaction creates the row. Later interactions with the same
     * email — or the same WhatsApp number — only enrich the existing lead
     * (updating the name, number, or interest when provided) and never reset
     * its original source, subscription state, or admin-written notes.
     *
     * When no email is provided the lead is matched by WhatsApp number, so
     * WhatsApp-only visitors are captured too; if they later share an email,
     * the row is adopted and backfilled instead of duplicated.
     *
     * @param  array{email?: string|null, user_id?: int|null, name?: string|null, whatsapp_number?: string|null, source?: string|null, interest?: string|null, notes?: string|null, is_subscribed?: bool|null}  $data
     */
    public function capture(array $data): Lead
    {
        $email = filled($data['email'] ?? null)
            ? Str::lower(trim((string) $data['email']))
            : null;

        $whatsapp = filled($data['whatsapp_number'] ?? null)
            ? preg_replace('/[^0-9]/', '', (string) $data['whatsapp_number'])
            : null;

        if ($email === null && $whatsapp === null) {
            throw new \InvalidArgumentException('A lead must have an email or a WhatsApp number.');
        }

        $lead = $this->findExisting($email, $whatsapp);

        if ($lead === null) {
            $lead = new Lead;
            $lead->email = $email;
            $lead->whatsapp_number = $whatsapp;
            $lead->source = $data['source'] ?? Lead::SOURCE_WEBSITE;
            $lead->interest = $data['interest'] ?? Lead::INTEREST_GENERAL;
            $lead->is_subscribed = $data['is_subscribed'] ?? true;
        }

        if ($email !== null && blank($lead->email)) {
            $lead->email = $email;
        }

        if ($whatsapp !== null) {
            $lead->whatsapp_number = $whatsapp;
        }

        if (isset($data['user_id'])) {
            $lead->user_id = $data['user_id'];
        }

        if (filled($data['name'] ?? null)) {
            $lead->name = $data['name'];
        } elseif (blank($lead->name) && $email !== null) {
            $lead->name = Str::before($email, '@');
        }

        if (filled($data['interest'] ?? null)) {
            $lead->interest = $data['interest'];
        }

        if (array_key_exists('is_subscribed', $data)) {
            $lead->is_subscribed = (bool) $data['is_subscribed'];
        }

        if (filled($data['notes'] ?? null) && blank($lead->notes)) {
            $lead->notes = $data['notes'];
        }

        $lead->save();

        return $lead;
    }

    private function findExisting(?string $email, ?string $whatsapp): ?Lead
    {
        if ($email !== null) {
            $byEmail = Lead::query()->where('email', $email)->first();

            if ($byEmail !== null) {
                return $byEmail;
            }
        }

        if ($whatsapp !== null) {
            return Lead::query()->where('whatsapp_number', $whatsapp)->first();
        }

        return null;
    }
}

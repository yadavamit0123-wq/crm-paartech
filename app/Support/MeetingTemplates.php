<?php

namespace App\Support;

use App\Models\Tenant;

class MeetingTemplates
{
    /**
     * Placeholders supported: {name}, {date}, {time}, {link}, {company}
     * (single braces — safe to echo in Blade, kabhi double-brace mat use karna)
     */
    public static function defaults(): array
    {
        return [
            'google_meet_whatsapp' => "Hello {name},\n\nYour meeting with {company} is confirmed.\n\nPlatform: Google Meet\nDate: {date}\nTime: {time}\nJoin Link: {link}\n\nPlease join on time. Koi question ho toh isi number par reply karein.\n\nRegards,\n{company}",
            'google_meet_email_subject' => 'Meeting Invitation - {company} | {date}, {time}',
            'google_meet_email' => "Dear {name},\n\nYour meeting with {company} has been scheduled. Please find the details below.\n\nPlatform: Google Meet\nDate: {date}\nTime: {time}\nJoin Link: {link}\n\nKindly join the meeting using the above link at the scheduled time. Should you need to reschedule, please let us know in advance.\n\nBest regards,\n{company}",
            'zoom_whatsapp' => "Hello {name},\n\nYour meeting with {company} is confirmed.\n\nPlatform: Zoom\nDate: {date}\nTime: {time}\nJoin Link: {link}\n\nPlease join on time. Koi question ho toh isi number par reply karein.\n\nRegards,\n{company}",
            'zoom_email_subject' => 'Meeting Invitation - {company} | {date}, {time}',
            'zoom_email' => "Dear {name},\n\nYour meeting with {company} has been scheduled. Please find the details below.\n\nPlatform: Zoom\nDate: {date}\nTime: {time}\nJoin Link: {link}\n\nKindly join the meeting using the above link at the scheduled time. Should you need to reschedule, please let us know in advance.\n\nBest regards,\n{company}",
        ];
    }

    public static function labels(): array
    {
        return [
            'google_meet_whatsapp' => 'Google Meet — WhatsApp message',
            'google_meet_email_subject' => 'Google Meet — Email subject',
            'google_meet_email' => 'Google Meet — Email body',
            'zoom_whatsapp' => 'Zoom — WhatsApp message',
            'zoom_email_subject' => 'Zoom — Email subject',
            'zoom_email' => 'Zoom — Email body',
        ];
    }

    public static function forTenant(?Tenant $tenant): array
    {
        $saved = $tenant?->settings['meeting_templates'] ?? [];
        if (! is_array($saved)) {
            $saved = [];
        }

        return array_merge(static::defaults(), array_filter($saved, fn ($v) => is_string($v) && $v !== ''));
    }

    public static function fill(string $template, array $vars): string
    {
        return str_replace(
            ['{name}', '{date}', '{time}', '{link}', '{company}'],
            [
                $vars['name'] ?? '',
                $vars['date'] ?? '',
                $vars['time'] ?? '',
                $vars['link'] ?? '',
                $vars['company'] ?? '',
            ],
            $template
        );
    }
}

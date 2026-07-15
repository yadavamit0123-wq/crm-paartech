<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Meeting create — Test mode (free, no keys) OR Live mode (Settings credentials).
 *
 * Zoom live: Server-to-Server OAuth (account_id + client_id + client_secret)
 * Google live: Calendar API via OAuth refresh token (client_id + client_secret + refresh_token)
 */
class MeetingService
{
    public function status(Tenant $tenant): array
    {
        $s = $tenant->settings ?? [];

        $zoomLive = filled($s['zoom_account_id'] ?? null)
            && filled($s['zoom_client_id'] ?? null)
            && filled($s['zoom_client_secret'] ?? null);

        $googleLive = filled($s['google_client_id'] ?? null)
            && filled($s['google_client_secret'] ?? null)
            && filled($s['google_refresh_token'] ?? null);

        return [
            'zoom' => [
                'mode' => $zoomLive ? 'live' : 'test',
                'label' => $zoomLive ? 'Live API' : 'Free Test Mode',
            ],
            'google_meet' => [
                'mode' => $googleLive ? 'live' : 'test',
                'label' => $googleLive ? 'Live API' : 'Free Test Mode',
            ],
        ];
    }

    /**
     * @return array{link: string, meeting_id: string, mode: string, platform: string, join_url: string, password: ?string, raw: array}
     */
    public function create(Tenant $tenant, Lead $lead, string $platform, string $mode, ?Carbon $when = null, int $durationMinutes = 45): array
    {
        $platform = $platform === 'zoom' ? 'zoom' : 'google_meet';
        $when = $when ?? now();
        $status = $this->status($tenant);
        $useLive = ($status[$platform]['mode'] ?? 'test') === 'live';

        try {
            if ($platform === 'zoom' && $useLive) {
                return $this->createZoomLive($tenant, $lead, $mode, $when, $durationMinutes);
            }
            if ($platform === 'google_meet' && $useLive) {
                return $this->createGoogleLive($tenant, $lead, $mode, $when, $durationMinutes);
            }
        } catch (Throwable $e) {
            Log::warning('Meeting live API failed, falling back to test mode', [
                'platform' => $platform,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->createTest($tenant, $lead, $platform, $mode, $when);
    }

    protected function createTest(Tenant $tenant, Lead $lead, string $platform, string $mode, Carbon $when): array
    {
        if ($platform === 'zoom') {
            // Zoom-style join URL (personal room style) — shareable in invites
            $personal = trim((string) ($tenant->settings['zoom_personal_link'] ?? ''));
            if ($personal !== '') {
                $link = $personal;
                $id = (string) preg_replace('/\D/', '', parse_url($personal, PHP_URL_PATH) ?? '') ?: Str::random(10);
            } else {
                $id = (string) random_int(10000000000, 99999999999);
                $pwd = Str::lower(Str::random(6));
                $link = "https://zoom.us/j/{$id}?pwd={$pwd}";
            }

            return [
                'link' => $link,
                'join_url' => $link,
                'meeting_id' => $id,
                'password' => null,
                'mode' => 'test',
                'platform' => 'zoom',
                'raw' => ['note' => 'Free test Zoom link — Settings me Zoom API credentials paste karke Live API on karo'],
            ];
        }

        // Google Meet style room code: xxx-yyyy-zzz
        $code = Str::lower(Str::random(3)).'-'.Str::lower(Str::random(4)).'-'.Str::lower(Str::random(3));
        $link = 'https://meet.google.com/'.$code;

        return [
            'link' => $link,
            'join_url' => $link,
            'meeting_id' => $code,
            'password' => null,
            'mode' => 'test',
            'platform' => 'google_meet',
            'raw' => [
                'note' => 'Free test Meet link — invite share ho sakta hai. Live Google Calendar Meet ke liye Settings me Client ID/Secret + Connect Google karein.',
                'scheduled_at' => $when->toIso8601String(),
                'calendar_hint' => $mode === 'scheduled'
                    ? 'https://calendar.google.com/calendar/render?'.http_build_query([
                        'action' => 'TEMPLATE',
                        'text' => 'Meeting: '.$lead->name.' x '.$tenant->name,
                        'dates' => $when->format('Ymd\THis').'/'.$when->copy()->addMinutes(45)->format('Ymd\THis'),
                        'details' => "Join Meet: {$link}",
                        'add' => $lead->email ?? '',
                    ])
                    : null,
            ],
        ];
    }

    protected function createZoomLive(Tenant $tenant, Lead $lead, string $mode, Carbon $when, int $durationMinutes): array
    {
        $token = $this->zoomAccessToken($tenant);
        $topic = 'CRM Meeting: '.$lead->name.' × '.$tenant->name;
        $type = $mode === 'scheduled' ? 2 : 1; // 1=instant, 2=scheduled

        $payload = [
            'topic' => $topic,
            'type' => $type,
            'duration' => $durationMinutes,
            'timezone' => config('app.timezone', 'Asia/Kolkata'),
            'agenda' => 'Scheduled from CRM for lead '.$lead->name.($lead->phone ? ' / '.$lead->phone : ''),
            'settings' => [
                'join_before_host' => true,
                'waiting_room' => false,
                'mute_upon_entry' => true,
            ],
        ];

        if ($mode === 'scheduled') {
            $payload['start_time'] = $when->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post('https://api.zoom.us/v2/users/me/meetings', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Zoom API error: '.$response->body());
        }

        $data = $response->json();
        $link = $data['join_url'] ?? '';

        if ($link === '') {
            throw new \RuntimeException('Zoom API ne join_url nahi diya');
        }

        return [
            'link' => $link,
            'join_url' => $link,
            'meeting_id' => (string) ($data['id'] ?? ''),
            'password' => $data['password'] ?? null,
            'mode' => 'live',
            'platform' => 'zoom',
            'raw' => $data,
        ];
    }

    protected function zoomAccessToken(Tenant $tenant): string
    {
        $s = $tenant->settings ?? [];
        $accountId = $s['zoom_account_id'];
        $clientId = $s['zoom_client_id'];
        $clientSecret = $s['zoom_client_secret'];

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $accountId,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Zoom OAuth failed: '.$response->body());
        }

        $token = $response->json('access_token');
        if (! $token) {
            throw new \RuntimeException('Zoom access_token missing');
        }

        return $token;
    }

    protected function createGoogleLive(Tenant $tenant, Lead $lead, string $mode, Carbon $when, int $durationMinutes): array
    {
        $accessToken = $this->googleAccessToken($tenant);
        $end = $when->copy()->addMinutes($durationMinutes);

        $payload = [
            'summary' => 'Meeting: '.$lead->name.' × '.$tenant->name,
            'description' => 'Scheduled from CRM'.($lead->phone ? "\nPhone: ".$lead->phone : ''),
            'start' => [
                'dateTime' => $when->toIso8601String(),
                'timeZone' => config('app.timezone', 'Asia/Kolkata'),
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => config('app.timezone', 'Asia/Kolkata'),
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => (string) Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
            'attendees' => array_values(array_filter([
                filled($lead->email) ? ['email' => $lead->email] : null,
            ])),
        ];

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post('https://www.googleapis.com/calendar/v3/calendars/primary/events?conferenceDataVersion=1', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Google Calendar API error: '.$response->body());
        }

        $data = $response->json();
        $link = data_get($data, 'hangoutLink')
            ?: data_get($data, 'conferenceData.entryPoints.0.uri')
            ?: ($data['htmlLink'] ?? '');

        if ($link === '') {
            throw new \RuntimeException('Google Meet link nahi mila');
        }

        return [
            'link' => $link,
            'join_url' => $link,
            'meeting_id' => (string) ($data['id'] ?? ''),
            'password' => null,
            'mode' => 'live',
            'platform' => 'google_meet',
            'raw' => $data,
        ];
    }

    protected function googleAccessToken(Tenant $tenant): string
    {
        $s = $tenant->settings ?? [];

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $s['google_client_id'],
            'client_secret' => $s['google_client_secret'],
            'refresh_token' => $s['google_refresh_token'],
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Google OAuth refresh failed: '.$response->body());
        }

        $token = $response->json('access_token');
        if (! $token) {
            throw new \RuntimeException('Google access_token missing');
        }

        return $token;
    }
}

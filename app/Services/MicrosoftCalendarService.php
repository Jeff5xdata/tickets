<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\EmailAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftCalendarService extends CalendarService
{
    public function syncEvents(): int
    {
        try {
            if (!$this->refreshTokenIfNeeded()) {
                return 0;
            }

            $calendars = $this->getCalendars();
            $totalEvents = 0;

            foreach ($calendars as $calendar) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->emailAccount->access_token,
                    'Accept' => 'application/json',
                ])->get("https://graph.microsoft.com/v1.0/me/calendars/{$calendar['id']}/events", [
                    '$filter' => "start/dateTime ge '" . now()->subDays(30)->toIso8601String() . "' and end/dateTime le '" . now()->addDays(90)->toIso8601String() . "'",
                    '$orderby' => 'start/dateTime',
                    '$top' => 1000
                ]);

                if ($response->successful()) {
                    $events = $response->json()['value'];
                    foreach ($events as $event) {
                        $this->syncMicrosoftEvent($event, $calendar['id']);
                        $totalEvents++;
                    }
                }
            }

            Log::info('Microsoft Calendar events synced successfully', [
                'email_account_id' => $this->emailAccount->id,
                'events_synced' => $totalEvents
            ]);

            return $totalEvents;

        } catch (\Exception $e) {
            Log::error('Error syncing Microsoft Calendar events', [
                'email_account_id' => $this->emailAccount->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function createEvent(array $eventData): ?CalendarEvent
    {
        try {
            if (!$this->refreshTokenIfNeeded()) {
                return null;
            }

            $calendarId = $eventData['calendar_id'] ?? 'primary';

            $microsoftEvent = [
                'subject' => $eventData['title'],
                'body' => [
                    'contentType' => 'text',
                    'content' => $eventData['description'] ?? ''
                ],
                'location' => [
                    'displayName' => $eventData['location'] ?? ''
                ],
                'start' => [
                    'dateTime' => $eventData['start_time']->toIso8601String(),
                    'timeZone' => 'UTC'
                ],
                'end' => [
                    'dateTime' => $eventData['end_time']->toIso8601String(),
                    'timeZone' => 'UTC'
                ],
                'isAllDay' => $eventData['all_day'] ?? false,
            ];

            if (!empty($eventData['attendees'])) {
                $microsoftEvent['attendees'] = array_map(function($attendee) {
                    return [
                        'emailAddress' => [
                            'address' => $attendee
                        ],
                        'type' => 'required'
                    ];
                }, $eventData['attendees']);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->emailAccount->access_token,
                'Content-Type' => 'application/json',
            ])->post("https://graph.microsoft.com/v1.0/me/calendars/{$calendarId}/events", $microsoftEvent);

            if (!$response->successful()) {
                throw new \Exception('Failed to create Microsoft Calendar event: ' . $response->body());
            }

            $createdEvent = $response->json();

            // Create local event
            $event = CalendarEvent::create([
                'user_id' => $this->emailAccount->user_id,
                'email_account_id' => $this->emailAccount->id,
                'external_id' => $createdEvent['id'],
                'calendar_id' => $calendarId,
                'title' => $eventData['title'],
                'description' => $eventData['description'] ?? '',
                'location' => $eventData['location'] ?? '',
                'start_time' => $eventData['start_time'],
                'end_time' => $eventData['end_time'],
                'all_day' => $eventData['all_day'] ?? false,
                'status' => 'confirmed',
                'provider' => 'microsoft',
                'attendees' => $eventData['attendees'] ?? [],
                'color_id' => $eventData['color_id'] ?? null,
            ]);

            return $event;

        } catch (\Exception $e) {
            Log::error('Error creating Microsoft Calendar event', [
                'email_account_id' => $this->emailAccount->id,
                'error' => $e->getMessage(),
                'event_data' => $eventData
            ]);
            return null;
        }
    }

    public function updateEvent(CalendarEvent $event, array $eventData): bool
    {
        try {
            if (!$this->refreshTokenIfNeeded()) {
                return false;
            }

            $updateData = [];

            if (isset($eventData['title'])) {
                $updateData['subject'] = $eventData['title'];
            }
            if (isset($eventData['description'])) {
                $updateData['body'] = [
                    'contentType' => 'text',
                    'content' => $eventData['description']
                ];
            }
            if (isset($eventData['location'])) {
                $updateData['location'] = [
                    'displayName' => $eventData['location']
                ];
            }
            if (isset($eventData['start_time'])) {
                $updateData['start'] = [
                    'dateTime' => $eventData['start_time']->toIso8601String(),
                    'timeZone' => 'UTC'
                ];
            }
            if (isset($eventData['end_time'])) {
                $updateData['end'] = [
                    'dateTime' => $eventData['end_time']->toIso8601String(),
                    'timeZone' => 'UTC'
                ];
            }
            if (isset($eventData['all_day'])) {
                $updateData['isAllDay'] = $eventData['all_day'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->emailAccount->access_token,
                'Content-Type' => 'application/json',
            ])->patch("https://graph.microsoft.com/v1.0/me/events/{$event->external_id}", $updateData);

            if (!$response->successful()) {
                throw new \Exception('Failed to update Microsoft Calendar event: ' . $response->body());
            }

            // Update local event
            $event->update($eventData);

            return true;

        } catch (\Exception $e) {
            Log::error('Error updating Microsoft Calendar event', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function deleteEvent(CalendarEvent $event): bool
    {
        try {
            if (!$this->refreshTokenIfNeeded()) {
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->emailAccount->access_token,
            ])->delete("https://graph.microsoft.com/v1.0/me/events/{$event->external_id}");

            if (!$response->successful()) {
                throw new \Exception('Failed to delete Microsoft Calendar event: ' . $response->body());
            }

            $event->delete();

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting Microsoft Calendar event', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getCalendars(): array
    {
        try {
            if (!$this->refreshTokenIfNeeded()) {
                return [];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->emailAccount->access_token,
                'Accept' => 'application/json',
            ])->get('https://graph.microsoft.com/v1.0/me/calendars');

            if (!$response->successful()) {
                throw new \Exception('Failed to get Microsoft calendars: ' . $response->body());
            }

            $calendars = $response->json()['value'];
            $calendarList = [];

            foreach ($calendars as $calendar) {
                if ($calendar['canEdit'] ?? false) {
                    $calendarList[] = [
                        'id' => $calendar['id'],
                        'name' => $calendar['name'],
                        'primary' => $calendar['isDefaultCalendar'] ?? false,
                        'color' => $calendar['color'] ?? null,
                    ];
                }
            }

            return $calendarList;

        } catch (\Exception $e) {
            Log::error('Error getting Microsoft calendars', [
                'email_account_id' => $this->emailAccount->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function syncMicrosoftEvent(array $event, string $calendarId): void
    {
        $startTime = $event['start'];
        $endTime = $event['end'];

        $eventData = [
            'external_id' => $event['id'],
            'calendar_id' => $calendarId,
            'title' => $event['subject'] ?? 'No Title',
            'description' => $event['body']['content'] ?? '',
            'location' => $event['location']['displayName'] ?? '',
            'start_time' => new \DateTime($startTime['dateTime']),
            'end_time' => new \DateTime($endTime['dateTime']),
            'all_day' => $event['isAllDay'] ?? false,
            'status' => $event['showAs'] ?? 'confirmed',
            'provider' => 'microsoft',
            'attendees' => array_map(function($attendee) {
                return $attendee['emailAddress']['address'];
            }, $event['attendees'] ?? []),
            'color_id' => $event['color'] ?? null,
        ];

        $this->syncEvent($eventData);
    }
} 
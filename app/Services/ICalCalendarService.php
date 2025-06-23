<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\EmailAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ICal\ICal;

class ICalCalendarService extends CalendarService
{
    private string $icalUrl;

    public function __construct(EmailAccount $emailAccount)
    {
        parent::__construct($emailAccount);
        $this->icalUrl = $emailAccount->imap_host; // Store iCal URL in imap_host field
    }

    public function syncEvents(): int
    {
        try {
            if (empty($this->icalUrl)) {
                Log::error('No iCal URL provided for account', [
                    'email_account_id' => $this->emailAccount->id
                ]);
                return 0;
            }

            $ical = new ICal($this->icalUrl);
            $events = $ical->events();
            $totalEvents = 0;

            foreach ($events as $event) {
                $this->syncICalEvent($event);
                $totalEvents++;
            }

            Log::info('iCal Calendar events synced successfully', [
                'email_account_id' => $this->emailAccount->id,
                'events_synced' => $totalEvents
            ]);

            return $totalEvents;

        } catch (\Exception $e) {
            Log::error('Error syncing iCal Calendar events', [
                'email_account_id' => $this->emailAccount->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function createEvent(array $eventData): ?CalendarEvent
    {
        // iCal is read-only, so we can't create events
        Log::warning('Cannot create events in iCal calendar (read-only)', [
            'email_account_id' => $this->emailAccount->id
        ]);
        return null;
    }

    public function updateEvent(CalendarEvent $event, array $eventData): bool
    {
        // iCal is read-only, so we can't update events
        Log::warning('Cannot update events in iCal calendar (read-only)', [
            'event_id' => $event->id
        ]);
        return false;
    }

    public function deleteEvent(CalendarEvent $event): bool
    {
        // iCal is read-only, so we can't delete events
        Log::warning('Cannot delete events in iCal calendar (read-only)', [
            'event_id' => $event->id
        ]);
        return false;
    }

    public function getCalendars(): array
    {
        // iCal typically has only one calendar
        return [
            [
                'id' => 'primary',
                'name' => 'iCal Calendar',
                'primary' => true,
                'color' => null,
            ]
        ];
    }

    private function syncICalEvent($event): void
    {
        $startTime = $this->parseICalDateTime($event->dtstart);
        $endTime = $this->parseICalDateTime($event->dtend);

        // Skip events that are too old or too far in the future
        if ($startTime < now()->subDays(30) || $startTime > now()->addDays(90)) {
            return;
        }

        $eventData = [
            'external_id' => $event->uid ?? uniqid('ical_'),
            'calendar_id' => 'primary',
            'title' => $event->summary ?? 'No Title',
            'description' => $event->description ?? '',
            'location' => $event->location ?? '',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'all_day' => $this->isAllDayEvent($event),
            'status' => $this->parseICalStatus($event->status ?? 'CONFIRMED'),
            'provider' => 'ical',
            'attendees' => $this->parseICalAttendees($event->attendee ?? []),
            'recurrence' => $this->parseICalRecurrence($event->rrule ?? []),
        ];

        $this->syncEvent($eventData);
    }

    private function parseICalDateTime($dateTimeString): \DateTime
    {
        // Remove timezone info and parse as UTC
        $cleanDateTime = preg_replace('/[A-Z]{3}$/', '', $dateTimeString);
        $cleanDateTime = str_replace('T', ' ', $cleanDateTime);
        $cleanDateTime = str_replace('Z', '', $cleanDateTime);
        
        return new \DateTime($cleanDateTime, new \DateTimeZone('UTC'));
    }

    private function isAllDayEvent($event): bool
    {
        // Check if the event is all day by looking at the date format
        $start = $event->dtstart ?? '';
        return !str_contains($start, 'T') && !str_contains($start, 'Z');
    }

    private function parseICalStatus(string $status): string
    {
        return match(strtoupper($status)) {
            'CONFIRMED' => 'confirmed',
            'TENTATIVE' => 'tentative',
            'CANCELLED' => 'cancelled',
            default => 'confirmed'
        };
    }

    private function parseICalAttendees($attendees): array
    {
        if (is_string($attendees)) {
            $attendees = [$attendees];
        }

        $emails = [];
        foreach ($attendees as $attendee) {
            if (preg_match('/mailto:(.+)/', $attendee, $matches)) {
                $emails[] = $matches[1];
            } elseif (filter_var($attendee, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $attendee;
            }
        }

        return $emails;
    }

    private function parseICalRecurrence($rrule): ?array
    {
        if (empty($rrule)) {
            return null;
        }

        // Basic RRULE parsing - this is a simplified version
        $recurrence = [];
        
        if (is_string($rrule)) {
            $rrule = [$rrule];
        }

        foreach ($rrule as $rule) {
            if (preg_match('/FREQ=([^;]+)/', $rule, $matches)) {
                $recurrence['frequency'] = strtolower($matches[1]);
            }
            if (preg_match('/INTERVAL=(\d+)/', $rule, $matches)) {
                $recurrence['interval'] = (int) $matches[1];
            }
            if (preg_match('/COUNT=(\d+)/', $rule, $matches)) {
                $recurrence['count'] = (int) $matches[1];
            }
            if (preg_match('/UNTIL=([^;]+)/', $rule, $matches)) {
                $recurrence['until'] = $this->parseICalDateTime($matches[1]);
            }
        }

        return empty($recurrence) ? null : $recurrence;
    }
} 
<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\EmailAccount;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService extends CalendarService
{
    private Google_Client $client;
    private Google_Service_Calendar $service;

    public function __construct(EmailAccount $emailAccount)
    {
        parent::__construct($emailAccount);
        
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google-email.client_id'));
        $this->client->setClientSecret(config('services.google-email.client_secret'));
        $this->client->setScopes([Google_Service_Calendar::CALENDAR]);
    }

    public function syncEvents(): int
    {
        try {
            if (!$this->refreshTokenIfNeeded()) {
                return 0;
            }

            $this->client->setAccessToken($this->emailAccount->access_token);
            $this->service = new Google_Service_Calendar($this->client);

            $calendars = $this->service->calendarList->listCalendarList();
            $totalEvents = 0;

            foreach ($calendars->getItems() as $calendar) {
                if ($calendar->getAccessRole() === 'reader' || $calendar->getAccessRole() === 'writer' || $calendar->getAccessRole() === 'owner') {
                    $events = $this->service->events->listEvents($calendar->getId(), [
                        'timeMin' => now()->subDays(30)->toRfc3339String(),
                        'timeMax' => now()->addDays(90)->toRfc3339String(),
                        'singleEvents' => true,
                        'orderBy' => 'startTime'
                    ]);

                    foreach ($events->getItems() as $event) {
                        $this->syncGoogleEvent($event, $calendar->getId());
                        $totalEvents++;
                    }
                }
            }

            Log::info('Google Calendar events synced successfully', [
                'email_account_id' => $this->emailAccount->id,
                'events_synced' => $totalEvents
            ]);

            return $totalEvents;

        } catch (\Exception $e) {
            Log::error('Error syncing Google Calendar events', [
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

            $this->client->setAccessToken($this->emailAccount->access_token);
            $this->service = new Google_Service_Calendar($this->client);

            $googleEvent = new Google_Service_Calendar_Event();
            $googleEvent->setSummary($eventData['title']);
            $googleEvent->setDescription($eventData['description'] ?? '');
            $googleEvent->setLocation($eventData['location'] ?? '');

            // Set start time
            $start = new \Google_Service_Calendar_EventDateTime();
            if ($eventData['all_day']) {
                $start->setDate($eventData['start_time']->format('Y-m-d'));
            } else {
                $start->setDateTime($eventData['start_time']->toRfc3339String());
                $start->setTimeZone('UTC');
            }
            $googleEvent->setStart($start);

            // Set end time
            $end = new \Google_Service_Calendar_EventDateTime();
            if ($eventData['all_day']) {
                $end->setDate($eventData['end_time']->format('Y-m-d'));
            } else {
                $end->setDateTime($eventData['end_time']->toRfc3339String());
                $end->setTimeZone('UTC');
            }
            $googleEvent->setEnd($end);

            // Set attendees if provided
            if (!empty($eventData['attendees'])) {
                $attendees = [];
                foreach ($eventData['attendees'] as $attendee) {
                    $attendees[] = ['email' => $attendee];
                }
                $googleEvent->setAttendees($attendees);
            }

            $calendarId = $eventData['calendar_id'] ?? 'primary';
            $createdEvent = $this->service->events->insert($calendarId, $googleEvent);

            // Create local event
            $event = CalendarEvent::create([
                'user_id' => $this->emailAccount->user_id,
                'email_account_id' => $this->emailAccount->id,
                'external_id' => $createdEvent->getId(),
                'calendar_id' => $calendarId,
                'title' => $eventData['title'],
                'description' => $eventData['description'] ?? '',
                'location' => $eventData['location'] ?? '',
                'start_time' => $eventData['start_time'],
                'end_time' => $eventData['end_time'],
                'all_day' => $eventData['all_day'] ?? false,
                'status' => 'confirmed',
                'provider' => 'google',
                'attendees' => $eventData['attendees'] ?? [],
                'color_id' => $eventData['color_id'] ?? null,
            ]);

            return $event;

        } catch (\Exception $e) {
            Log::error('Error creating Google Calendar event', [
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

            $this->client->setAccessToken($this->emailAccount->access_token);
            $this->service = new Google_Service_Calendar($this->client);

            $googleEvent = $this->service->events->get($event->calendar_id, $event->external_id);

            if (isset($eventData['title'])) {
                $googleEvent->setSummary($eventData['title']);
            }
            if (isset($eventData['description'])) {
                $googleEvent->setDescription($eventData['description']);
            }
            if (isset($eventData['location'])) {
                $googleEvent->setLocation($eventData['location']);
            }

            // Update start time
            if (isset($eventData['start_time'])) {
                $start = new \Google_Service_Calendar_EventDateTime();
                if ($eventData['all_day'] ?? $event->all_day) {
                    $start->setDate($eventData['start_time']->format('Y-m-d'));
                } else {
                    $start->setDateTime($eventData['start_time']->toRfc3339String());
                    $start->setTimeZone('UTC');
                }
                $googleEvent->setStart($start);
            }

            // Update end time
            if (isset($eventData['end_time'])) {
                $end = new \Google_Service_Calendar_EventDateTime();
                if ($eventData['all_day'] ?? $event->all_day) {
                    $end->setDate($eventData['end_time']->format('Y-m-d'));
                } else {
                    $end->setDateTime($eventData['end_time']->toRfc3339String());
                    $end->setTimeZone('UTC');
                }
                $googleEvent->setEnd($end);
            }

            $this->service->events->update($event->calendar_id, $event->external_id, $googleEvent);

            // Update local event
            $event->update($eventData);

            return true;

        } catch (\Exception $e) {
            Log::error('Error updating Google Calendar event', [
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

            $this->client->setAccessToken($this->emailAccount->access_token);
            $this->service = new Google_Service_Calendar($this->client);

            $this->service->events->delete($event->calendar_id, $event->external_id);
            $event->delete();

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting Google Calendar event', [
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

            $this->client->setAccessToken($this->emailAccount->access_token);
            $this->service = new Google_Service_Calendar($this->client);

            $calendars = $this->service->calendarList->listCalendarList();
            $calendarList = [];

            foreach ($calendars->getItems() as $calendar) {
                if ($calendar->getAccessRole() === 'reader' || $calendar->getAccessRole() === 'writer' || $calendar->getAccessRole() === 'owner') {
                    $calendarList[] = [
                        'id' => $calendar->getId(),
                        'name' => $calendar->getSummary(),
                        'primary' => $calendar->getPrimary() ?? false,
                        'color' => $calendar->getBackgroundColor(),
                    ];
                }
            }

            return $calendarList;

        } catch (\Exception $e) {
            Log::error('Error getting Google calendars', [
                'email_account_id' => $this->emailAccount->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function syncGoogleEvent($googleEvent, string $calendarId): void
    {
        $startTime = $googleEvent->getStart();
        $endTime = $googleEvent->getEnd();

        $eventData = [
            'external_id' => $googleEvent->getId(),
            'calendar_id' => $calendarId,
            'title' => $googleEvent->getSummary() ?? 'No Title',
            'description' => $googleEvent->getDescription() ?? '',
            'location' => $googleEvent->getLocation() ?? '',
            'start_time' => $startTime->getDateTime() ? new \DateTime($startTime->getDateTime()) : new \DateTime($startTime->getDate()),
            'end_time' => $endTime->getDateTime() ? new \DateTime($endTime->getDateTime()) : new \DateTime($endTime->getDate()),
            'all_day' => !$startTime->getDateTime(),
            'status' => $googleEvent->getStatus() ?? 'confirmed',
            'provider' => 'google',
            'attendees' => array_map(function($attendee) {
                return $attendee->getEmail();
            }, $googleEvent->getAttendees() ?? []),
            'color_id' => $googleEvent->getColorId(),
        ];

        $this->syncEvent($eventData);
    }
} 
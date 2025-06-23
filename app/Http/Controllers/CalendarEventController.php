<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\EmailAccount;
use App\Services\CalendarServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->calendarEvents()->with('emailAccount');

        // Filter by provider
        if ($request->has('provider') && $request->provider) {
            $query->where('provider', $request->provider);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            try {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $query->where('start_time', '>=', $startDate);
            } catch (\Exception $e) {
                // Invalid date format, ignore this filter
            }
        } else {
            // Default filter: don't show events older than 24 hours
            $query->where('start_time', '>=', now()->subDay());
        }
        
        if ($request->has('end_date') && $request->end_date) {
            try {
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $query->where('end_time', '<=', $endDate);
            } catch (\Exception $e) {
                // Invalid date format, ignore this filter
            }
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $events = $query->orderBy('start_time')->paginate(20);

        return view('calendar-events.index', compact('events'));
    }

    public function show(CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendarEvent);
        
        return view('calendar-events.show', compact('calendarEvent'));
    }

    public function create()
    {
        $emailAccounts = auth()->user()->emailAccounts()
            ->whereIn('provider', ['google', 'microsoft'])
            ->where('is_active', true)
            ->get();

        return view('calendar-events.create', compact('emailAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email_account_id' => 'required|exists:email_accounts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'all_day' => 'boolean',
            'attendees' => 'nullable|array',
            'attendees.*' => 'email',
            'calendar_id' => 'nullable|string',
        ]);

        $emailAccount = EmailAccount::findOrFail($request->email_account_id);
        
        // Check if user owns this email account
        if ($emailAccount->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $calendarService = CalendarServiceFactory::create($emailAccount);
            
            $eventData = [
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'start_time' => new \DateTime($request->start_time),
                'end_time' => new \DateTime($request->end_time),
                'all_day' => $request->boolean('all_day'),
                'attendees' => $request->attendees ?? [],
                'calendar_id' => $request->calendar_id,
            ];

            $event = $calendarService->createEvent($eventData);

            if ($event) {
                return redirect()->route('calendar-events.show', $event)
                    ->with('success', 'Event created successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to create event.']);
            }

        } catch (\Exception $e) {
            Log::error('Error creating calendar event', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to create event: ' . $e->getMessage()]);
        }
    }

    public function edit(CalendarEvent $calendarEvent)
    {
        $this->authorize('update', $calendarEvent);

        $emailAccounts = auth()->user()->emailAccounts()
            ->whereIn('provider', ['google', 'microsoft'])
            ->where('is_active', true)
            ->get();

        return view('calendar-events.edit', compact('calendarEvent', 'emailAccounts'));
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $this->authorize('update', $calendarEvent);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'all_day' => 'boolean',
            'attendees' => 'nullable|array',
            'attendees.*' => 'email',
        ]);

        try {
            $calendarService = CalendarServiceFactory::create($calendarEvent->emailAccount);
            
            $eventData = [
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'start_time' => new \DateTime($request->start_time),
                'end_time' => new \DateTime($request->end_time),
                'all_day' => $request->boolean('all_day'),
                'attendees' => $request->attendees ?? [],
            ];

            $success = $calendarService->updateEvent($calendarEvent, $eventData);

            if ($success) {
                return redirect()->route('calendar-events.show', $calendarEvent)
                    ->with('success', 'Event updated successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to update event.']);
            }

        } catch (\Exception $e) {
            Log::error('Error updating calendar event', [
                'error' => $e->getMessage(),
                'event_id' => $calendarEvent->id,
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to update event: ' . $e->getMessage()]);
        }
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $this->authorize('delete', $calendarEvent);

        try {
            $calendarService = CalendarServiceFactory::create($calendarEvent->emailAccount);
            $success = $calendarService->deleteEvent($calendarEvent);

            if ($success) {
                return redirect()->route('calendar-events.index')
                    ->with('success', 'Event deleted successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to delete event.']);
            }

        } catch (\Exception $e) {
            Log::error('Error deleting calendar event', [
                'error' => $e->getMessage(),
                'event_id' => $calendarEvent->id
            ]);

            return back()->withErrors(['error' => 'Failed to delete event: ' . $e->getMessage()]);
        }
    }

    public function sync(Request $request)
    {
        $emailAccountId = $request->input('email_account_id');
        
        if ($emailAccountId) {
            $emailAccount = EmailAccount::where('user_id', auth()->id())
                ->where('id', $emailAccountId)
                ->firstOrFail();
            
            $calendarService = CalendarServiceFactory::create($emailAccount);
            $eventsSynced = $calendarService->syncEvents();
            
            return back()->with('success', "Synced {$eventsSynced} events from {$emailAccount->name}.");
        } else {
            // Sync all accounts
            $emailAccounts = auth()->user()->emailAccounts()
                ->whereIn('provider', ['google', 'microsoft', 'ical'])
                ->where('is_active', true)
                ->get();
            
            $totalEvents = 0;
            foreach ($emailAccounts as $emailAccount) {
                try {
                    $calendarService = CalendarServiceFactory::create($emailAccount);
                    $totalEvents += $calendarService->syncEvents();
                } catch (\Exception $e) {
                    Log::error('Error syncing calendar for account', [
                        'account_id' => $emailAccount->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return back()->with('success', "Synced {$totalEvents} events from all calendars.");
        }
    }

    public function getCalendars(Request $request)
    {
        $emailAccountId = $request->input('email_account_id');
        $emailAccount = EmailAccount::where('user_id', auth()->id())
            ->where('id', $emailAccountId)
            ->firstOrFail();

        try {
            $calendarService = CalendarServiceFactory::create($emailAccount);
            $calendars = $calendarService->getCalendars();
            
            return response()->json($calendars);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 
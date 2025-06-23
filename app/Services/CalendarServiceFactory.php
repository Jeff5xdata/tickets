<?php

namespace App\Services;

use App\Models\EmailAccount;

class CalendarServiceFactory
{
    public static function create(EmailAccount $emailAccount): CalendarService
    {
        return match($emailAccount->provider) {
            'google' => new GoogleCalendarService($emailAccount),
            'microsoft' => new MicrosoftCalendarService($emailAccount),
            'ical' => new ICalCalendarService($emailAccount),
            default => throw new \InvalidArgumentException("Unsupported calendar provider: {$emailAccount->provider}")
        };
    }
} 
<?php

namespace Csatar\Csatar\Classes;

use Google_Client;
use Google_Service_Calendar;
class GoogleCalendar
{
    public static function getEvents(array $calendarIds, $timeMin = null, $timeMax = null)
    {
        $events = [];

        foreach ($calendarIds as $calendarId) {
            $calendarEvents = self::getCalendarEvents($calendarId, $timeMin, $timeMax);

            $events = array_merge($events, $calendarEvents);
        }

        return collect($events);
    }
    public static function getClient() {
        $authFile = plugins_path('csatar/csatar/assets/service_account_keys.json');

        $client = new Google_Client();
        $client->setAuthConfig($authFile);
        $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

        return $client;
    }

    public static function getCalendarEvents($calendarId, $timeMin = null, $timeMax = null)
    {
        $client = self::getClient();

        //convert timeMin and timeMax to ISO 8901 format
        if ($timeMin) {
            $timeMin = date('c', strtotime($timeMin));
        }

        $optParams  = [
            'maxResults'   => 100,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin ?? date('c'),
        ];

        if ($timeMax) {
            $optParams['timeMax'] = date('c', strtotime($timeMax));
        }

        $service = new Google_Service_Calendar($client);
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = [];

        if (count($results->getItems()) == 0) {

        } else {

            foreach ($results->getItems() as $event) {
                $start = $event->start->dateTime;
                $end   = $event->end->dateTime;

                if (empty($start)) {
                    $start = $event->start->date;
                }

                if (empty($end)) {
                    $end = $event->end->date;
                }

                $events[] = [
                    'calendarId' => $calendarId,
                    'start'      => $start,
                    'end'        => $end,
                    'summary'    => $event->getSummary(),
                    'location'   => $event->getLocation(),
                    'organizer'  => $event->getOrganizer()->getDisplayName() ?? $event->getOrganizer()->getEmail(),
                ];

            }
        }

        return $events;
    }
}

<?php

// $Id: IcalendarAlarm.php,v 1.8 2005/07/21 22:31:44 defacer Exp $

class IcalendarAlarm extends IcalendarComponent
{
    public $name = 'VALARM';
    public $properties;
    public $mapping_arr = [
        'TRIGGER' => ['component' => 'reminder_time', 'function' => 'iCalendarEventTrigger'],
    ];

    public function construct()
    {
        $this->valid_components = [];
        $this->valid_properties = [
            'TRIGGER' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ACTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'X-WR-ALARMUID' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];

        parent::construct();
    }

    public function iCalendarEventTrigger($activity)
    {
        $reminder_time = $activity['reminder_time'];
        if ($reminder_time > 60) {
            $reminder_time = round($reminder_time / 60);
            $reminder = $reminder_time.'H';
        } else {
            $reminder = $reminder_time.'M';
        }
        $this->addProperty('ACTION', 'DISPLAY');
        $this->addProperty('TRIGGER', 'PT'.$reminder);
        $this->addProperty('DESCRIPTION', 'Reminder');

        return true;
    }
}

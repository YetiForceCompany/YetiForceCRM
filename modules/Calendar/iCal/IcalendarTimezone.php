<?php

// $Id: IcalendarTimezone.php,v 1.8 2005/07/21 22:31:44 defacer Exp $

class IcalendarTimezone extends IcalendarComponent
{
    public $name = 'VTIMEZONE';
    public $properties;

    public function construct()
    {
        $this->valid_components = [];
        $this->valid_properties = [
            'TZID' => RFC2445_REQUIRED | RFC2445_ONCE,
            'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZURL' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'STANDARDC' => RFC2445_OPTIONAL,
            'DAYLIGHTC' => RFC2445_OPTIONAL,
            'TZOFFSETFROM' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZOFFSETTO' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'X-PROP' => RFC2445_OPTIONAL,
        ];

        parent::construct();
    }
}

// REMINDER: DTEND must be later than DTSTART for all components which support both
// REMINDER: DUE must be later than DTSTART for all components which support both

<?php

// $Id: Icalendar.php,v 1.8 2005/07/21 22:31:44 defacer Exp $

class Icalendar extends IcalendarComponent
{
    public $name = 'VCALENDAR';

    public function construct()
    {
        $this->valid_properties = [
            'CALSCALE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'METHOD' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRODID' => RFC2445_REQUIRED | RFC2445_ONCE,
            'VERSION' => RFC2445_REQUIRED | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];

        $this->valid_components = [
            'VEVENT', 'VTODO', 'VTIMEZONE',
        ];

        parent::construct();
    }
}

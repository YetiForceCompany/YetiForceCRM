<?php

class IcalendarPropertyTzoffsetto extends IcalendarProperty
{
    public $name = 'TZOFFSETTO';
    public $val_type = RFC2445_TYPE_TEXT;

    public function construct()
    {
        $this->valid_parameters = [
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }
}

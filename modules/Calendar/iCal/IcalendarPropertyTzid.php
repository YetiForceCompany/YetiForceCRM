<?php

class IcalendarPropertyTzid extends IcalendarProperty
{
    public $name = 'TZID';
    public $val_type = RFC2445_TYPE_TEXT;

    public function construct()
    {
        $this->valid_parameters = [
            RFC2445_XNAME => RFC2445_REQUIRED,
        ];
    }
}

<?php

class IcalendarPropertyFreebusy extends IcalendarProperty
{
    public $name = 'FREEBUSY';
    public $val_type = RFC2445_TYPE_PERIOD;
    public $val_multi = true;

    public function construct()
    {
        $this->valid_parameters = [
            'FBTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function isValidValue($value)
    {
        if (!parent::isValidValue($value)) {
            return false;
        }

        $pos = strpos($value, '/'); // We know there's only one / in there
        if ($value{$pos - 1} != 'Z') {
            // Start time MUST be in UTC
            return false;
        }
        if ($value{$pos + 1} != 'P' && $substr($value, -1) != 'Z') {
            // If the second part is not a period, it MUST be in UTC
            return false;
        }

        return true;
    }
}

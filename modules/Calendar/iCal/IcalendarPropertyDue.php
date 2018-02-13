<?php

class IcalendarPropertyDue extends IcalendarProperty
{
    public $name = 'DUE';
    public $val_type = RFC2445_TYPE_DATE_TIME;

    public function construct()
    {
        $this->valid_parameters = [
            'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function isValidValue($value)
    {
        if (!parent::isValidValue($value)) {
            return false;
        }

        // If present in a FREEBUSY component, must be in UTC format
        if ($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
            return false;
        }

        return true;
    }

    public function isValidParameter($parameter, $value)
    {
        $parameter = strtoupper($parameter);

        if (!parent::isValidParameter($parameter, $value)) {
            return false;
        }
        if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
            return false;
        }

        return true;
    }
}

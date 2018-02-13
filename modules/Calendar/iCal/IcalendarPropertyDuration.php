<?php

class IcalendarPropertyDuration extends IcalendarProperty
{
    public $name = 'DURATION';
    public $val_type = RFC2445_TYPE_DURATION;

    public function construct()
    {
        $this->valid_parameters = [
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function isValidValue($value)
    {
        if (!parent::isValidValue($value)) {
            return false;
        }

        // Value must be positive
        return $value{0}
        != '-';
    }
}

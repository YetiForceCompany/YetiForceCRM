<?php

class IcalendarPropertyVersion extends IcalendarProperty
{
    public $name = 'VERSION';
    public $val_type = RFC2445_TYPE_TEXT;
    public $val_default = '2.0';

    public function construct()
    {
        $this->valid_parameters = [
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function isValidValue($value)
    {
        return $value === '2.0' || $value === 2.0;
    }
}

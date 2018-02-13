<?php

class IcalendarPropertyTransp extends IcalendarProperty
{
    public $name = 'TRANSP';
    public $val_type = RFC2445_TYPE_TEXT;
    public $val_default = 'OPAQUE';

    public function construct()
    {
        $this->valid_parameters = [
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function isValidValue($value)
    {
        return $value === 'TRANSPARENT' || $value === 'OPAQUE';
    }
}

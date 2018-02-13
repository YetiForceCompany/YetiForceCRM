<?php

class IcalendarPropertySequence extends IcalendarProperty
{
    public $name = 'SEQUENCE';
    public $val_type = RFC2445_TYPE_INTEGER;
    public $val_default = 0;

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
        $value = intval($value);

        return $value >= 0;
    }
}

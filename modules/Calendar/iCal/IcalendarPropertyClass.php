<?php

class IcalendarPropertyClass extends IcalendarProperty
{

	public $name = 'CLASS';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_default = 'PUBLIC';

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function isValidValue($value)
	{
		$value = strtoupper($value);
		// If this is not an xname, it is case-sensitive
		return ($value === 'PUBLIC' || $value === 'PRIVATE' || $value === 'CONFIDENTIAL' || \ICalendarRfc::rfc2445IsXname(strtoupper($value)));
	}
}

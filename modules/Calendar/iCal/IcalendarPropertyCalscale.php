<?php

// 4.7 Calendar Properties
// -----------------------

class IcalendarPropertyCalscale extends IcalendarProperty
{
	public $name = 'CALSCALE';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}

	public function isValidValue($value)
	{
		// This is case-sensitive
		return $value === 'GREGORIAN';
	}
}

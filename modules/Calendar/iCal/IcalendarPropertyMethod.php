<?php

class IcalendarPropertyMethod extends IcalendarProperty
{

	public $name = 'METHOD';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function isValidValue($value)
	{
		// This is case-sensitive
		// Methods from RFC 2446
		$methods = ['PUBLISH', 'REQUEST', 'REPLY', 'ADD', 'CANCEL', 'REFRESH', 'COUNTER', 'DECLINECOUNTER'];
		return in_array($value, $methods);
	}
}

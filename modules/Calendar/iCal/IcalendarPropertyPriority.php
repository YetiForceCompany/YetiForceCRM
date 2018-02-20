<?php

class IcalendarPropertyPriority extends IcalendarProperty
{
	public $name = 'PRIORITY';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}

	public function isValidValue($value)
	{
		// Only integers between 0 and 9 inclusive allowed
		if (!parent::isValidValue($value)) {
			return false;
		}

		return true;
	}
}

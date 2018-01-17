<?php

class IcalendarPropertyPercentcomplete extends IcalendarProperty
{

	public $name = 'PERCENT-COMPLETE';
	public $val_type = RFC2445_TYPE_INTEGER;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function isValidValue($value)
	{
		// Only integers between 0 and 100 inclusive allowed
		if (!parent::isValidValue($value)) {
			return false;
		}
		$value = intval($value);
		return ($value >= 0 && $value <= 100);
	}
}

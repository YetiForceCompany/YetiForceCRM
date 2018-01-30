<?php

class IcalendarPropertyLastmodified extends IcalendarProperty
{

	public $name = 'LAST-MODIFIED';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function isValidValue($value)
	{
		if (!parent::isValidValue($value)) {
			return false;
		}
		// Time MUST be in UTC format
		return(substr($value, -1) == 'Z');
	}
}

<?php

// 4.8.5 Recurrence Component Properties
// -------------------------------------

class IcalendarPropertyExdate extends IcalendarProperty
{

	public $name = 'EXDATE';
	public $val_type = RFC2445_TYPE_DATE_TIME;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = [
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function isValidParameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::isValidParameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
			return false;
		}

		return true;
	}
}

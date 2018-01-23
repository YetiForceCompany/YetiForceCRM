<?php

class IcalendarPropertyGeo extends IcalendarProperty
{

	public $name = 'GEO';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function isValidValue($value)
	{
		// This MUST be two floats separated by a semicolon
		if (!is_string($value)) {
			return false;
		}

		$floats = explode(';', $value);
		if (count($floats) != 2) {
			return false;
		}

		return \ICalendarRfc::rfc2445IsValidValue($floats[0], RFC2445_TYPE_FLOAT) && \ICalendarRfc::rfc2445IsValidValue($floats[1], RFC2445_TYPE_FLOAT);
	}

	public function setValueICal($value)
	{
		// Must override this, otherwise the semicolon separating
		// the two floats would get auto-quoted, which is illegal
		if ($this->isValidValue($value)) {
			$this->value = $value;
			return true;
		}

		return false;
	}
}

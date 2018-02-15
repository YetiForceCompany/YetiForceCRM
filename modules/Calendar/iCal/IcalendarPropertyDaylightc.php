<?php

class IcalendarPropertyDaylightc extends IcalendarProperty
{
	public $name = 'DAYLIGHTC';
	public $val_type = RFC2445_TYPE_INTEGER;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}
}

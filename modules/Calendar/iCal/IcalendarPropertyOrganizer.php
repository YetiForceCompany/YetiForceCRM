<?php

class IcalendarPropertyOrganizer extends IcalendarProperty
{

	public $name = 'ORGANIZER';
	public $val_type = RFC2445_TYPE_CAL_ADDRESS;

	public function construct()
	{
		$this->valid_parameters = [
			'CN' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DIR' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SENT-BY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}
}

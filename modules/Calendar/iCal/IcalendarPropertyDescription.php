<?php

class IcalendarPropertyDescription extends IcalendarProperty
{
	public $name = 'DESCRIPTION';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}
}

<?php

class IcalendarPropertyResources extends IcalendarProperty
{
	public $name = 'RESOURCES';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = [
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}
}

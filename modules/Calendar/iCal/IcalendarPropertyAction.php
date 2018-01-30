<?php

class IcalendarPropertyAction extends IcalendarProperty
{

	public $name = 'ACTION';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			'DISPLAY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}
}

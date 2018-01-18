<?php

class IcalendarPropertyUrl extends IcalendarProperty
{

	public $name = 'URL';
	public $val_type = RFC2445_TYPE_URI;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}
}

<?php

class IcalendarPropertyExrule extends IcalendarProperty
{

	public $name = 'EXRULE';
	public $val_type = RFC2445_TYPE_RECUR;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}
}

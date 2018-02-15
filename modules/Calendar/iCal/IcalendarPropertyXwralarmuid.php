<?php

class IcalendarPropertyXwralarmuid extends IcalendarProperty
{
	public $name = 'X_WR_ALARMUID';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}
}

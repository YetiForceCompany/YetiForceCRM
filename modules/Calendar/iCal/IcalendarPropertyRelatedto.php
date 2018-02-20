<?php

class IcalendarPropertyRelatedto extends IcalendarProperty
{
	public $name = 'RELATED-TO';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			'RELTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}
}

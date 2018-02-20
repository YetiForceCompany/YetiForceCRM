<?php

class IcalendarPropertyProdid extends IcalendarProperty
{
	public $name = 'PRODID';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_default;

	public function construct()
	{
		$this->val_default = '-//YetiForce CRM//YetiForce CRM ' . \App\Version::get() . '//EN';

		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];
	}
}

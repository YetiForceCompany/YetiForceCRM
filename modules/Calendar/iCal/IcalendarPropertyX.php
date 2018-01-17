<?php

// 4.8.8 Miscellaneous Component Properties
// ----------------------------------------

class IcalendarPropertyX extends IcalendarProperty
{

	public $name = RFC2445_XNAME;
	public $val_type = NULL;

	public function construct()
	{
		$this->valid_parameters = [
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		];
	}

	public function setName($name)
	{

		$name = strtoupper($name);

		if (rfc2445_is_xname($name)) {
			$this->name = $name;
			return true;
		}

		return false;
	}
}

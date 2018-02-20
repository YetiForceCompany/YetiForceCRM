<?php

class IcalendarPropertyUid extends IcalendarProperty
{
	public $name = 'UID';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = [
			RFC2445_XNAME => RFC2445_OPTIONAL,
		];

		// The exception to the rule: this is not a static value, so we
		// generate it on-the-fly here. Guaranteed to be different for
		// each instance of this property, too. Nice.
		$this->val_default = \ICalendarRfc::rfc2445Guid();
	}
}

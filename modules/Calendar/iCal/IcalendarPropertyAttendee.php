<?php

class IcalendarPropertyAttendee extends IcalendarProperty
{
    public $name = 'ATTENDEE';
    public $val_type = RFC2445_TYPE_CAL_ADDRESS;

    public function construct()
    {
        $this->valid_parameters = [
            'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CN' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ROLE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PARTSTAT' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RSVP' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CUTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'MEMBER' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DELEGATED-TO' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DELEGATED-FROM' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SENT-BY' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DIR' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function setParentComponent($componentname)
    {
        if (!parent::setParentComponent($componentname)) {
            return false;
        }

        if ($this->parent_component == 'VFREEBUSY' || $this->parent_component == 'VALARM') {
            // Most parameters become invalid in this case, the full allowed set is now:
            $this->valid_parameters = [
                'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
                RFC2445_XNAME => RFC2445_OPTIONAL,
            ];
        }

        return false;
    }
}

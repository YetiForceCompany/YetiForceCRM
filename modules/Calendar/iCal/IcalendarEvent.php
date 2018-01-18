<?php

// $Id: IcalendarEvent.php,v 1.8 2005/07/21 22:31:44 defacer Exp $

class IcalendarEvent extends IcalendarComponent
{

	public $name = 'VEVENT';
	public $properties;
	public $mapping_arr = [
		'CLASS' => ['component' => 'visibility', 'type' => 'string'],
		'DTSTART' => ['component' => ['date_start', 'time_start'], 'function' => 'iCalendarEventDtStart', 'type' => 'datetime'],
		'DTEND' => ['component' => ['due_date', 'time_end'], 'function' => 'iCalendarEventDtEnd', 'type' => 'datetime'],
		'DTSTAMP' => ['component' => ['date_start', 'time_start'], 'function' => 'iCalendarEventDtStamp', 'type' => 'datetime'],
		'LOCATION' => ['component' => 'location', 'type' => 'string'],
		'ORGANIZER' => ['component' => 'activityid', 'function' => 'icalendarEventOrganizer', 'type' => 'user'],
		'STATUS' => ['component' => 'activitystatus', 'type' => 'string'],
		'SUMMARY' => ['component' => 'subject', 'type' => 'string'],
		'PRIORITY' => ['component' => 'priority', 'type' => 'string'],
		'ATTENDEE' => ['component' => 'activityid', 'function' => 'iCalendarEventAttendee', 'type' => 'user'],
		'RESOURCES' => ['component' => ['location', 'activitystatus'], 'type' => 'string'],
		'DESCRIPTION' => ['component' => 'description', 'type' => 'string'],
	];
	public $field_mapping_arr = [
		'priority' => 'taskpriority'
	];

	public function construct()
	{

		$this->valid_components = ['VALARM'];

		$this->valid_properties = [
			'CLASS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'CREATED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			// Standard ambiguous here: in 4.6.1 it says that DTSTAMP in optional,
			// while in 4.8.7.2 it says it's REQUIRED. Go with REQUIRED.
			'DTSTAMP' => RFC2445_REQUIRED | RFC2445_ONCE,
			// Standard ambiguous here: in 4.6.1 it says that DTSTART in optional,
			// while in 4.8.2.4 it says it's REQUIRED. Go with REQUIRED.
			'DTSTART' => RFC2445_REQUIRED | RFC2445_ONCE,
			'GEO' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LOCATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ORGANIZER' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PRIORITY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SEQUENCE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'STATUS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SUMMARY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TRANSP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			// Standard ambiguous here: in 4.6.1 it says that UID in optional,
			// while in 4.8.4.7 it says it's REQUIRED. Go with REQUIRED.
			'UID' => RFC2445_REQUIRED | RFC2445_ONCE,
			'URL' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'RECURRENCE-ID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DTEND' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DURATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ATTACH' => RFC2445_OPTIONAL,
			'ATTENDEE' => RFC2445_OPTIONAL,
			'CATEGORIES' => RFC2445_OPTIONAL,
			'COMMENT' => RFC2445_OPTIONAL,
			'CONTACT' => RFC2445_OPTIONAL,
			'EXDATE' => RFC2445_OPTIONAL,
			'EXRULE' => RFC2445_OPTIONAL,
			'REQUEST-STATUS' => RFC2445_OPTIONAL,
			'RELATED-TO' => RFC2445_OPTIONAL,
			'RESOURCES' => RFC2445_OPTIONAL,
			'RDATE' => RFC2445_OPTIONAL,
			'RRULE' => RFC2445_OPTIONAL,
			RFC2445_XNAME => RFC2445_OPTIONAL
		];

		parent::construct();
	}

	public function invariantHolds()
	{
		// DTEND and DURATION must not appear together
		if (isset($this->properties['DTEND']) && isset($this->properties['DURATION'])) {
			return false;
		}


		if (isset($this->properties['DTEND']) && isset($this->properties['DTSTART'])) {

			if ($this->properties['DTEND'][0]->value <= $this->properties['DTSTART'][0]->value) {
				return false;
			}

			// DTEND and DTSTART must have the same value type
			if ($this->properties['DTEND'][0]->val_type != $this->properties['DTSTART'][0]->val_type) {
				return false;
			}
		}
		return true;
	}

	public function iCalendarEventDtStamp($activity)
	{
		$components = gmdate('Ymd', strtotime($activity['date_start'] . ' ' . $activity['time_start'])) . 'T' . gmdate('His', strtotime($activity['date_start'] . " " . $activity['time_start'])) . 'Z';
		$this->addProperty('DTSTAMP', $components);
		return true;
	}

	public function iCalendarEventDtStart($activity)
	{
		$time = str_replace(':', '', $activity['time_start']);
		if (strlen($time) < 6) {
			while ((6 - strlen($time)) > 0) {
				$time .= '0';
			}
		}
		$components = str_replace('-', '', $activity['date_start']) . 'T' . $time . 'Z';
		$this->addProperty('DTSTART', $components);
		return true;
	}

	public function iCalendarEventDtEnd($activity)
	{
		$time = str_replace(':', '', $activity['time_end']);
		if (strlen($time) < 6) {
			while ((6 - strlen($time)) > 0) {
				$time .= '0';
			}
		}
		$components = str_replace('-', '', $activity['due_date']) . 'T' . $time . 'Z';
		$this->addProperty('DTEND', $components);
		return true;
	}

	/**
	 * iCalendar event attendee
	 * @param array $activity
	 * @return boolean
	 */
	public function iCalendarEventAttendee($activity)
	{
		$query = (new App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $activity['id']]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!empty($row['email'])) {
				$this->addProperty('ATTENDEE', 'mailto:' . $row['email'], ['CN' => vtlib\Functions::getCRMRecordLabel($row['crmid'])]);
			}
		}
		$dataReader->close();
		return true;
	}

	public function icalendarEventOrganizer($activity)
	{
		$email = App\Fields\Email::getUserMail($activity['assigned_user_id']);
		$this->addProperty('ORGANIZER', 'mailto:' . $email);
		return true;
	}
}

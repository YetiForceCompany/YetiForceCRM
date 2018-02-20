<?php

// $Id: IcalendarTodo.php,v 1.8 2005/07/21 22:31:44 defacer Exp $

class IcalendarTodo extends IcalendarComponent
{
	public $name = 'VTODO';
	public $properties;
	public $mapping_arr = [
		'DESCRIPTION' => ['component' => 'description', 'type' => 'string'],
		//'DTSTAMP'		=>	array('component'=>array('date_start','time_start'),'function'=>'iCalendarEventDtStamp','type'=>'datetime'),
		'DTSTART' => ['component' => ['date_start', 'time_start'], 'function' => 'iCalendarEventDtStart', 'type' => 'datetime'],
		'DUE' => ['component' => ['due_date'], 'function' => 'iCalendarEventDtEnd', 'type' => 'datetime'],
		'STATUS' => ['component' => 'status', 'type' => 'string'],
		'SUMMARY' => ['component' => 'subject', 'type' => 'string'],
		'PRIORITY' => ['component' => 'priority', 'type' => 'string'],
		'RESOURCES' => ['component' => ['status'], 'type' => 'string'],
	];
	public $field_mapping_arr = [
		'status' => 'activitystatus',
		'priority' => 'taskpriority',
	];

	public function construct()
	{
		$this->valid_components = [];
		$this->valid_properties = [
			'CLASS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'COMPLETED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'CREATED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DTSTAMP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DTSTART' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'GEO' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LOCATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ORGANIZER' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PERCENT' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PRIORITY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'RECURID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SEQUENCE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'STATUS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SUMMARY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'UID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'URL' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DURATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ATTACH' => RFC2445_OPTIONAL,
			'ATTENDEE' => RFC2445_OPTIONAL,
			'CATEGORIES' => RFC2445_OPTIONAL,
			'COMMENT' => RFC2445_OPTIONAL,
			'CONTACT' => RFC2445_OPTIONAL,
			'EXDATE' => RFC2445_OPTIONAL,
			'EXRULE' => RFC2445_OPTIONAL,
			'RSTATUS' => RFC2445_OPTIONAL,
			'RELATED' => RFC2445_OPTIONAL,
			'RESOURCES' => RFC2445_OPTIONAL,
			'RDATE' => RFC2445_OPTIONAL,
			'RRULE' => RFC2445_OPTIONAL,
			'XPROP' => RFC2445_OPTIONAL,
		];

		parent::construct();
	}

	public function iCalendarEventDtStamp($activity)
	{
		$components = gmdate('Ymd', strtotime($activity['date_start'] . ' ' . $activity['time_start'])) . 'T' . gmdate('His', strtotime($activity['date_start'] . ' ' . $activity['time_start'])) . 'Z';
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
		$components = str_replace('-', '', $activity['due_date']) . 'T000000Z';
		$this->addProperty('DUE', $components);

		return true;
	}
}

<?php
class qCal_DateTime_Recur_Yearly extends qCal_DateTime_Recur {

	/**
	 * @todo This is a god method that really should be split out into each
	 * of the qCal_DateTime_Recur_Rule_ByXXX classes. For now I did all the logic
	 * here to keep it simple and not confuse myself more than necessary.
	 */
	protected function doGetRecurrences($rules, $start, $end) {
	
		// an array to store recurrences
		$recurrences = array();
		
		// start day, year, and month
		$sday = $start->format('d');
		$smonth = $start->format('m');
		$syear = $start->format('Y');
		
		// end day, year, and month
		$eday = $end->format('d');
		$emonth = $end->format('m');
		$eyear = $end->format('Y');
		
		// loop over years, by increment
		$year = $syear;
		while ($year <= $eyear) {
		
			// if byMonth is specified...
			if (count($this->byMonth())) {
				// loop over each month
				for ($month = 1; $month <= 12; $month++) {
					// if this is the start year still and we haven't reached the start month, skip ahead
					if ($year == $syear && $month < $smonth) {
						continue;
					}
					// if this is the end year and we have passed the end month, break out of loop
					if ($year == $eyear && $month > $emonth) {
						break;
					}
					// if this is not one of the bymonths, continue as well
					if (!in_array($month, $this->byMonth())) {
						continue;
					}
					// now we need to loop over each day of the month to look for byday or bymonthday
					$thismonth = new qCal_Date(); // used to determine total days in the current month
					$thismonth->setDate($year, $month, 1);
					$weekdays = array(
						'MO' => 0,
						'TU' => 0,
						'WE' => 0,
						'TH' => 0,
						'FR' => 0,
						'SA' => 0,
						'SU' => 0,
					);
					// @todo For now this only allows 1SU, SU, but not -1SU (no negatives for now)
					for ($day = 1; $day <= $thismonth->format('t'); $day++) {
						$alreadyadded = false;
						$date = new qCal_Date;
						$date->setDate($year, $month, $day);
						$date->setTime(0, 0, 0);
						$wdname = strtoupper(substr($date->format('l'), 0, 2));
						// keep track of how many of each day of the week have gone by
						$weekdays[$wdname]++;
						// if byDay is specified...
						// @todo this is inconsistent, I don't use the getter here because of its special functionality.
						// I need to either remove the special functionality or not use getters elsewhere in this method
						$byday = $this->byday;
						if (count($byday)) {
							// by day is broken into an array of arrays like array('TH' => 0), array('FR' => 1), array('MO' => -2) etc.
							// with zero meaning every instance of that particular day should be included and number meaning the Nth of that day
							foreach ($byday as $val) {
								// if at least one of this wday has gone by...
								$num = current($val);
								if ($weekdays[$wdname] > 0) {
									// check if it is the right week day and if a digit is specified (like 1SU) that it is checked as well
									if ($wdname == key($val) && ($weekdays[$wdname] == $num || $num == 0)) {
										$recurrences[] = $date;
										$alreadyadded = true;
									}
								}
							}
						}
						
						// if byMonthDay is specified...
						if (count($this->byMonthDay())) {
							foreach ($this->byMonthDay() as $mday) {
								// only add this day if it hasn't been added already
								if ($mday == $day && !$alreadyadded) {
									$recurrences[] = $date;
								}
							}
						}
						
						// now loop over each hour and add hours
						if (count($this->byHour())) {
							$hourrecurrences = array();
							foreach ($this->byHour() as $hour) {
								$new = new qCal_Date();
								$new = $new->copy($date);
								$new->setTime($hour, 0, 0);
								$hourrecurrences[] = $new;
							}
						}
						
						// now loop over byHours and add byMinutes
						if (count($this->byMinute())) {
							if (!isset($minuterecurrences)) $minuterecurrences = array();
							foreach ($this->byMinute() as $minute) {
								$new = new qCal_Date();
								$new = $new->copy($date);
								$new->setTime(0, $minute, 0);
							}
						}
						
						// now loop over byMinutes and add bySeconds
						
					}
				}
			}
			
			// if in the first year we don't find an instance, don't do the interval, just increment a year
			if ($year == $syear && count($recurrences)) $year += $this->interval();
			else ($year++);
		}
		
		// now loop over weeks to get byWeekNo
		
		foreach ($recurrences as $date) {
			// pr($date->format("r"));
		}
		// exit;
		
		return $recurrences;
		// for bymonth, it would make the most sense to loop over each month until the specified one
		// is found. Then loop over each day to find its sub-rules.
		
		// for byweekno, it would make the most sense to loop over each week until the specified one
		// is found. Then apply any sub-rules (actually I'm not sure how byhour and its ilk would be applied in this situation... need to read the rfc)
	
	}

}
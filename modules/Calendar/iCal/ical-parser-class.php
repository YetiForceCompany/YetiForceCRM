<?php

class iCal
{

	public $folders;

	public function iCal()
	{
		$this->folders = 'cache/import/';
	}

	public function iCalReader($filename, $rootDirectory = '')
	{
		$iCaltoArray = $this->iCalDecoder($filename, $rootDirectory);
		return $iCaltoArray;
	}

	public function iCalDecoder($file, $rootDirectory)
	{
		$ical = file_get_contents($rootDirectory . $this->folders . $file);
		preg_match_all('/BEGIN:VEVENT.*?END:VEVENT/si', $ical, $eventresult, PREG_PATTERN_ORDER);
		preg_match_all('/BEGIN:VTODO.*?END:VTODO/si', $ical, $todoresult, PREG_PATTERN_ORDER);
		$countEventResult = count($eventresult[0]);
		for ($i = 0; $i < $countEventResult; $i++) {
			$tmpbyline = explode("\n", $eventresult[0][$i]);
			$begin = false;
			$key = NULL;
			foreach ($tmpbyline as $item) {
				$item = str_replace("\r", "", $item);
				$item = str_replace("\\n", "<br />", $item);
				$item = str_replace("\,", ",", $item);
				$tmpholderarray = explode(":", $item, 2);

				if (count($tmpholderarray) > 1) {
					if ($tmpholderarray[0] == 'BEGIN') {
						if ($begin === false) {
							$begin = true;
							$majorarray['TYPE'] = $tmpholderarray[1];
						} else {
							$majorarray[$tmpholderarray[1]] = array();
							$key = $tmpholderarray[1];
						}
					} else if ($tmpholderarray[0] == 'END') {
						if (!empty($key)) {
							$key = NULL;
						}
					} else {
						$tmpholderarrayKey = $tmpholderarray[0];
						if (strpos($tmpholderarrayKey, ';') !== false) {
							$tmpholderarrayKeyArray = explode(";", $tmpholderarrayKey);
							$tmpholderarrayKey = $tmpholderarrayKeyArray[0];
						}
						if (!empty($key)) {
							$majorarray[$key][$tmpholderarrayKey] = $tmpholderarray[1];
						} else {
							$majorarray[$tmpholderarrayKey] = $tmpholderarray[1];
						}
					}
				}
			}
			$icalarray[] = $majorarray;
			unset($majorarray);
		}
		
		$countTodoResult = count($todoresult[0]);
		for ($i = 0; $i < $countTodoResult; $i++) {
			$tmpbyline = explode("\n", $todoresult[0][$i]);
			$begin = false;
			$key = NULL;
			foreach ($tmpbyline as $item) {
				$item = str_replace("\r", "", $item);
				$item = str_replace("\\n", "<br />", $item);
				$item = str_replace("\,", ",", $item);
				$tmpholderarray = explode(":", $item);

				if (count($tmpholderarray) > 1) {
					if ($tmpholderarray[0] == 'BEGIN') {
						if ($begin === false) {
							$begin = true;
							$majorarray['TYPE'] = $tmpholderarray[1];
						} else {
							$majorarray[$tmpholderarray[1]] = array();
							$key = $tmpholderarray[1];
						}
					} else if ($tmpholderarray[0] == 'END') {
						if (!empty($key)) {
							$key = NULL;
						}
					} else {
						$tmpholderarrayKey = $tmpholderarray[0];
						if (strpos($tmpholderarrayKey, ';') !== false) {
							$tmpholderarrayKeyArray = explode(";", $tmpholderarrayKey);
							$tmpholderarrayKey = $tmpholderarrayKeyArray[0];
						}
						if (!empty($key)) {
							$majorarray[$key][$tmpholderarrayKey] = $tmpholderarray[1];
						} else {
							$majorarray[$tmpholderarrayKey] = $tmpholderarray[1];
						}
					}
				}
			}
			$icalarray[] = $majorarray;
			unset($majorarray);
		}
		return $icalarray;
	}
}

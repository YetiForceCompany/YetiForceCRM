<?php

/**
 * Calendar CalendarWidget Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_CalendarFilters_Model extends Vtiger_Base_Model
{

	protected $filterPath = 'modules/Calendar/calendarFilters';
	protected $filters = false;

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public function __construct()
	{
		$dir = new DirectoryIterator($this->filterPath);
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$name = $fileinfo->getFilename();
				$name = rtrim($name, '.php');
				$filterClassName = Vtiger_Loader::getComponentClassName('CalendarFilter', $name, 'Calendar');
				$this->filters[] = new $filterClassName();
			}
		}
	}

	public function isActive()
	{
		return count($this->filters);
	}

	public function getFilters()
	{
		return $this->filters;
	}
}

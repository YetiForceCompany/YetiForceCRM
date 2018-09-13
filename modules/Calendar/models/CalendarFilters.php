<?php

/**
 * Calendar CalendarWidget Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_CalendarFilters_Model extends \App\Base
{
	protected $filterPath = 'modules/Calendar/calendarfilters';
	protected $filters = false;

	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Constructor.
	 *
	 * @return bool
	 */
	public function __construct()
	{
		if (!is_dir($this->filterPath)) {
			return false;
		}
		$dir = new DirectoryIterator($this->filterPath);
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot() && $fileinfo->getExtension() === 'php') {
				$name = trim($fileinfo->getBasename('.php'));
				$filterClassName = Vtiger_Loader::getComponentClassName('CalendarFilter', $name, 'Calendar');
				$filterInstance = new $filterClassName();
				if (method_exists($filterInstance, 'checkPermissions') && $filterInstance->checkPermissions()) {
					$this->filters[] = $filterInstance;
				}
			}
		}
	}

	public function isActive()
	{
		return $this->filters ? count($this->filters) : false;
	}

	public function getFilters()
	{
		return $this->filters;
	}
}

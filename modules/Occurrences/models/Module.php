<?php

/**
 * Occurrences module model class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Occurrences_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Calendar view url.
	 *
	 * @return string
	 */
	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=CalendarExtended';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		array_unshift($links['SIDEBARLINK'], Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_CALENDAR',
			'linkurl' => $this->getCalendarViewUrl(),
			'linkicon' => 'fas fa-calendar-alt'
		]));
		return $links;
	}
}

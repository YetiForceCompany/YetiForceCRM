<?php

/**
 * Reservations module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_Module_Model extends Vtiger_Module_Model
{
	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=Calendar';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getId(), ['SIDEBARLINK', 'SIDEBARWIDGET'], $linkParams);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_CALENDAR_VIEW',
			'linkurl' => $this->getCalendarViewUrl(),
			'linkicon' => 'fas fa-calendar-alt',
		]);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_RECORDS_LIST',
			'linkurl' => $this->getListViewUrl(),
			'linkicon' => 'fas fa-list',
		]);
		if (isset($linkParams['ACTION']) && 'Calendar' === $linkParams['ACTION']) {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_USERS',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getUsersList',
				'linkicon' => '',
				'linkclass' => 'js-calendar__filter--users',
			]);
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_TYPE',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getTypesList',
				'linkicon' => '',
				'linkclass' => 'js-calendar__filter--types',
			]);
		}
		return $links;
	}

	/**
	 * Function to get the Default View Component Name.
	 *
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return 'Calendar';
	}
}

<?php

/**
 * CallHistory ListView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class CallHistory_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Overrided to remove add button.
	 */
	public function getBasicLinks()
	{
		return [];
	}

	/**
	 * Overrided to remove Mass Edit Option.
	 */
	public function getListViewMassActions($linkParams)
	{
		return Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['LISTVIEWMASSACTION'], $linkParams);
	}

	/**
	 * Function to give advance links of a module.
	 *
	 * @return array of advanced links
	 */
	public function getAdvancedLinks()
	{
		$advancedLinks = parent::getAdvancedLinks();
		foreach ($advancedLinks as $key => $value) {
			if ($value['linklabel'] === 'LBL_FIND_DUPLICATES') {
				unset($advancedLinks[$key]);
			}
		}
		return $advancedLinks;
	}
}

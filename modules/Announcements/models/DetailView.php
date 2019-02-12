<?php

/**
 * Announcements DetailView Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * Function to get the detail view related links.
	 *
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();

		$relatedLinks[] = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => 'LBL_USERS',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showUsers',
			'linkicon' => 'fa-user',
			'linkKey' => 'LBL_USERS',
			'related' => 'Users',
		];

		return $relatedLinks;
	}
}

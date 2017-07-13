<?php

/**
 * Announcements DetailView Model Class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_DetailView_Model extends Vtiger_DetailView_Model
{

	/**
	 * Function to get the detail view related links
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
			'linkicon' => 'glyphicon-user',
			'linkKey' => 'LBL_USERS',
			'related' => 'Users'
		];
		return $relatedLinks;
	}
}

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
	 * {@inheritdoc}
	 */
	public function getDetailViewRelatedLinks()
	{
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$relatedLinks[] = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => 'LBL_USERS',
			'linkurl' => $this->getRecord()->getDetailViewUrl() . '&mode=showUsers',
			'linkicon' => 'fa-user',
			'linkKey' => 'LBL_USERS',
			'related' => 'Users',
		];
		return $relatedLinks;
	}
}

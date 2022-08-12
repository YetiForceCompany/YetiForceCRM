<?php
/**
 * Vendors detail view model file.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Vendors detail view model class.
 */
class Vendors_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewRelatedLinks()
	{
		$relatedLinks = parent::getDetailViewRelatedLinks();
		if (Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission('OpenStreetMap')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_MAP',
				'linkurl' => $this->getRecord()->getDetailViewUrl() . '&mode=showOpenStreetMap',
				'linkicon' => '',
			];
		}
		return $relatedLinks;
	}
}

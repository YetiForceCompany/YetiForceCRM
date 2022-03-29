<?php
/**
 * Service contracts detail view model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Service contracts detail view model class.
 */
class ServiceContracts_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach (App\Utils\ServiceContracts::getModules() as $moduleName) {
			if ($userPrivilegesModel->hasModuleActionPermission($recordModel->getModuleName(), 'ServiceContractsSla') && $userPrivilegesModel->hasModulePermission($moduleName)) {
				$relatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => \App\Language::translate('LBL_SLA_POLICY', $this->getModuleName()) . ' - ' . \App\Language::translate($moduleName, $moduleName),
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showSlaPolicyView&target=' . $moduleName,
					'linkicon' => 'fas fa-door-open',
				];
			}
		}
		return $relatedLinks;
	}
}

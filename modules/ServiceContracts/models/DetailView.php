<?php
/**
 * ServiceContracts DetailView model class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach (App\Utils\ServiceContracts::getModules() as  $moduleName) {
			if ($userPrivilegesModel->hasModulePermission($moduleName)) {
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

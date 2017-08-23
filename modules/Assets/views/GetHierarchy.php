<?php

/**
 * View to get hierarchy from Account
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Assets_GetHierarchy_View extends Vtiger_Index_View
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$hierarchyModuleName = 'Accounts';
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleName) || !$userPrivilegesModel->hasModulePermission($hierarchyModuleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!\App\Privilege::isPermitted($hierarchyModuleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$fields = $request->get('fields');
		$hierarchyModuleName = 'Accounts';
		$focus = CRMEntity::getInstance($hierarchyModuleName);
		$hierarchy = $focus->getAccountHierarchy($recordId, $fields);

		$classFunction = AppConfig::module($moduleName, 'RENEWAL_CUSTOMER_FUNCTION');
		if ($classFunction && class_exists($classFunction['class']) && method_exists($classFunction['class'], $classFunction['hierarchy'])) {
			$method = $classFunction['hierarchy'];
			$hierarchy = $classFunction['class']::$method($hierarchy);
		}
		foreach ($hierarchy['entries'] as $accountId => $accountInfo) {
			$link = $accountInfo[0]['data'];
			preg_match('/<a href="+/', $link, $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $link, $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i", $link, $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance($hierarchyModuleName);
				$recordModel->setId($accountId);
				$hierarchy['entries'][$accountId][0]['data'] = $dashes[0] . $name[2];
			}
		}
		$viewer->assign('MODULE', $hierarchyModuleName);
		$viewer->assign('ACCOUNT_HIERARCHY', $hierarchy);
		$viewer->view('AccountHierarchy.tpl', $hierarchyModuleName);
	}
}

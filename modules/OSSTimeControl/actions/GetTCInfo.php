<?php

/**
 * OSSTimeControl GetTCInfo action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_GetTCInfo_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());
		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$srecord = $request->getInteger('id');
		$smodule = $request->getByType('sourceModule', 2);

		$recordPermission = \App\Privilege::isPermitted($smodule, 'DetailView', $srecord);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();

		$id = $request->getInteger('id');
		$sourceModule = $request->getByType('sourceModule', 2);

		$sourceData = [];

		if (\App\Record::isExists($id)) {
			$record = Vtiger_Record_Model::getInstanceById($id, $sourceModule);
			$entity = $record->getEntity();
			$sourceData = $entity->column_fields;
			if ($sourceModule === 'HelpDesk') {
				$sourceData['contact_label'] = \App\Record::getLabel($sourceData['contact_id']);
				if (\App\Record::getType($sourceData['parent_id']) !== 'Accounts') {
					unset($sourceData['parent_id']);
				} else {
					$sourceData['account_label'] = \App\Record::getLabel($sourceData['parent_id']);
				}
			} elseif ($sourceModule === 'Project') {
				$ifExist = (new \App\Db\Query())->from('vtiger_account')->where(['accountid' => $sourceData['linktoaccountscontacts']])->exists();
				if ($ifExist) {
					$sourceData['account_label'] = \App\Record::getLabel($sourceData['linktoaccountscontacts']);
				} else {
					$sourceData['contact_label'] = \App\Record::getLabel($sourceData['linktoaccountscontacts']);
				}
			}
		}

		if ($sourceData === false) {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_FAILED_TO_IMPORT_INFO', $moduleName)];
		} else {
			$result = ['success' => true, 'sourceData' => $sourceData];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

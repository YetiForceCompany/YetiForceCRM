<?php

/**
 * OSSTimeControl GetTCInfo action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSTimeControl_GetTCInfo_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());
		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$srecord = $request->get('id');
		$smodule = $request->get('sourceModule');

		$recordPermission = Users_Privileges_Model::isPermitted($smodule, 'DetailView', $srecord);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();

		$id = $request->get('id');
		$sourceModule = $request->get('sourceModule');

		$sourceData = [];

		if (isRecordExists($id)) {
			$record = Vtiger_Record_Model::getInstanceById($id, $sourceModule);
			$entity = $record->getEntity();
			$sourceData = $entity->column_fields;
			if ($sourceModule === 'HelpDesk') {
				$sourceData['contact_label'] = \App\Record::getLabel($sourceData['contact_id']);
				if (vtlib\Functions::getCRMRecordType($sourceData['parent_id']) !== 'Accounts') {
					unset($sourceData['parent_id']);
				} else {
					$sourceData['account_label'] = \App\Record::getLabel($sourceData['parent_id']);
				}
			} else if ($sourceModule === 'Project') {
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

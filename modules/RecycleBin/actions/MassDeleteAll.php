<?php

/**
 * Mass records delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin_MassDeleteAll_Action extends Vtiger_Mass_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		// TODO: how correctly check it
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getByType('sourceModule', 2), 'MassDelete')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$recordIds = (new \App\Db\Query())->select('crmid')->from('vtiger_crmentity')->where(['deleted' => 1])->column();
		if ($recordIds) {
			$skipped = [];
			foreach ($recordIds as $recordId) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
				if (!$recordModel->privilegeToDelete()) {
					$skipped[] = $recordModel->getName();
					continue;
				}
				$recordModel->delete();
				unset($recordModel);
			}
			$text = \App\Language::translate('LBL_RECORD_HAS_BEEN_DELETED');
			$type = 'success';
			if ($skipped) {
				$type = 'info';
				$text .= PHP_EOL . \App\Language::translate('LBL_OMITTED_RECORDS');
				foreach ($skipped as $name) {
					$text .= PHP_EOL . $name;
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => $text, 'type' => $type]]);
		$response->emit();
	}
}

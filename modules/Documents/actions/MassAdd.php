<?php

/**
 * Action to mass upload files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Documents_MassAdd_Action extends Vtiger_Mass_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$nameFiles = $request->getArray('nameFile', 'Text');
		foreach ($_FILES as $file) {
			$countFiles = count($file['name']);
			for ($i = 0; $i < $countFiles; ++$i) {
				$recordeModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$fieldModel = $recordeModel->getModule()->getField('notes_title')->getUITypeModel();
				$fieldModel->validate($nameFiles[$i], true);
				$recordeModel->set('notes_title', $fieldModel->getDBValue($nameFiles[$i], $recordeModel));
				$recordeModel->set('assigned_user_id', App\User::getCurrentUserId());
				$recordeModel->file = [
					'name' => $file['name'][$i],
					'type' => $file['type'][$i],
					'tmp_name' => $file['tmp_name'][$i],
					'error' => $file['error'][$i],
					'size' => $file['size'][$i],
				];
				$recordeModel->set('filelocationtype', 'I');
				$recordeModel->set('filestatus', true);
				$recordeModel->save();
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}

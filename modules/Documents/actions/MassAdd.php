<?php

/**
 * Action to mass upload files
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Documents_MassAdd_Action extends Vtiger_Mass_Action
{

	/**
	 * Function to check permission
	 * @param Vtiger_Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		if (!Users_Privileges_Model::isPermitted($request->getModule(), 'CreateView')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$nameFiles = $request->get('nameFile');
		foreach ($_FILES as $file) {
			$countFiles = count($file['name']);
			for ($i = 0; $i < $countFiles; $i++) {
				$originalFile = [
					'name' => $file['name'][$i],
					'type' => $file['type'][$i],
					'tmp_name' => $file['tmp_name'][$i],
					'error' => $file['error'][$i],
					'size' => $file['size'][$i],
				];
				$recordeModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$recordeModel->set('notes_title', $nameFiles[$i]);
				$recordeModel->set('assigned_user_id', App\User::getCurrentUserId());
				$recordeModel->file = $originalFile;
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

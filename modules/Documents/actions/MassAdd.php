<?php
/**
 * Mass file transfer action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Mass file transfer action class.
 */
class Documents_MassAdd_Action extends Vtiger_Mass_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$nameFiles = $request->getArray('nameFile', 'Text');
		$ids = [];
		foreach ($_FILES as $file) {
			$countFiles = \count($file['name']);
			for ($i = 0; $i < $countFiles; ++$i) {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$fieldModel = $recordModel->getModule()->getField('notes_title')->getUITypeModel();
				$fieldModel->validate($nameFiles[$i], true);
				$recordModel->set('notes_title', $fieldModel->getDBValue($nameFiles[$i], $recordModel));
				$recordModel->set('assigned_user_id', App\User::getCurrentUserId());
				$recordModel->file = [
					'name' => $file['name'][$i],
					'type' => $file['type'][$i],
					'tmp_name' => $file['tmp_name'][$i],
					'error' => $file['error'][$i],
					'size' => $file['size'][$i],
				];
				$recordModel->set('filelocationtype', 'I');
				$recordModel->set('filestatus', true);
				$recordModel->save();
				$ids[$recordModel->getId()] = $recordModel->getName();
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($ids);
		$response->emit();
	}
}

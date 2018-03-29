<?php
/**
 * Basic class to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic class to handle files.
 */
abstract class Vtiger_Basic_File
{
	/**
	 * Storage name.
	 *
	 * @var string
	 */
	public $storageName = '';

	/**
	 * Checking permission in get method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function getCheckPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$field = $request->getInteger('field');
		if ($record) {
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $record) || !\App\Field::getFieldPermission($moduleName, $field)) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		return true;
	}

	/**
	 * Checking permission in post method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function postCheckPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$field = $request->getByType('field', 'Alnum');
		if (!empty($record)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if (!$recordModel->isEditable() || !\App\Field::getFieldPermission($moduleName, $field, false)) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			if (!\App\Field::getFieldPermission($moduleName, $field, false) || !\App\Privilege::isPermitted($moduleName, 'CreateView')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}

		return true;
	}

	/**
	 * Get and save files.
	 *
	 * @param \App\Request $request
	 */
	public function post(\App\Request $request)
	{
		$attach = \App\Fields\File::uploadAndSave($request, $_FILES, $this->getFileType(), $this->getStorageName());
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult([
				'field' => $request->get('field'),
				'module' => $request->getModule(),
				'attach' => count($attach) === 1 ? $attach[0] : $attach,
			]);
			$response->emit();
		}
	}

	/**
	 * Get storage name.
	 *
	 * @return string
	 */
	public function getStorageName()
	{
		return $this->storageName;
	}

	/**
	 * Get file type.
	 *
	 * @return string
	 */
	public function getFileType()
	{
		return $this->fileType;
	}
}

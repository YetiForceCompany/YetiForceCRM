<?php
/**
 * Image attachment basic file.
 *
 * @package Settings.Files
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Image attachment class to handle files.
 */
class Settings_Media_Images_File extends Vtiger_Basic_File
{
	use \App\Controller\Traits\SettingsPermission;

	/** {@inheritdoc} */
	public $storageName = 'public_html/Media/Images';

	/** {@inheritdoc} */
	public $fileType = 'image';

	/**
	 * Get attachment.
	 *
	 * @param \App\Request $request
	 */
	public function get(App\Request $request)
	{
		throw new \App\Exceptions\NoPermitted('Not Acceptable', 406);
	}

	public function postCheckPermission(App\Request $request)
	{
		$this->checkPermission($request);
		return true;
	}

	/** {@inheritdoc} */
	public function post(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);
		$fieldModel = $moduleModel->getFieldInstanceByName('image');
		if ($request->isAjax()) {
			if ($request->getBoolean('remove')) {
				Settings_Vtiger_Tracker_Model::addBasic('delete');
				$key = $request->getByType('key', \App\Purifier::ALNUM);
				if ($result = $fieldModel->getUITypeModel()->removeImage($key)) {
					Settings_Vtiger_Tracker_Model::addDetail(['key' => $key], ['key' => '']);
				}
			} else {
				Settings_Vtiger_Tracker_Model::addBasic('save');
				$attach = $fieldModel->getUITypeModel()->uploadTempFile($_FILES, 0);
				$result = [
					'field' => $fieldModel->getName(),
					'module' => $fieldModel->getModuleName(),
					'attach' => $attach,
				];
				if ($attach && $keys = array_column($attach, 'key')) {
					Settings_Vtiger_Tracker_Model::addDetail(['key' => ''], ['key' => implode(',', $keys)]);
				}
			}

			$response = new Vtiger_Response();
			$response->setResult($result);
			$response->emit();
		}
	}
}

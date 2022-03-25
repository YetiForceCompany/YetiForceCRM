<?php
/**
 * Multi image class to handle files.
 *
 * @package Files
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Image class to handle files.
 */
class Vtiger_MultiImage_File extends Vtiger_MultiAttachment_File
{
	/**
	 * Storage name.
	 *
	 * @var string
	 */
	public $storageName = 'MultiImage';

	/**
	 * File type.
	 *
	 * @var string
	 */
	public $fileType = 'image';

	/**
	 * View image.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function get(App\Request $request)
	{
		if ($request->isEmpty('key', 2)) {
			throw new \App\Exceptions\NoPermitted('Not Acceptable', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$key = $request->getByType('key', 2);
		$value = \App\Json::decode($recordModel->get($request->getByType('field', 2)));
		foreach ($value as $item) {
			if ($item['key'] === $key) {
				$file = \App\Fields\File::loadFromInfo([
					'path' => ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $item['path'],
					'name' => $item['name'],
				]);
				if (file_exists($file->getPath())) {
					header('Pragma: cache');
					header('Cache-control: max-age=86400, public');
					header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
					header('Content-type: ' . $file->getMimeType());
					header('Content-transfer-encoding: binary');
					header('Content-length: ' . $file->getSize());
					if ($request->getBoolean('download')) {
						header('Content-disposition: attachment; filename="' . $item['name'] . '"');
					}
					readfile($file->getPath());
					break;
				}
				throw new \App\Exceptions\AppException('ERR_FILE_NOT_FOUND', 404);
			}
		}
	}

	/** {@inheritdoc} */
	public function post(App\Request $request)
	{
		$fieldModel = Vtiger_Module_Model::getInstance($request->getModule())->getFieldByName($request->getByType('field', \App\Purifier::ALNUM));
		$attach = $fieldModel->getUITypeModel()->uploadTempFile($_FILES, $request->isEmpty('record') ? 0 : $request->getInteger('record'), $request->getByType('hash', \App\Purifier::ALNUM));
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult([
				'field' => $fieldModel->getName(),
				'module' => $fieldModel->getModuleName(),
				'attach' => $attach,
			]);
			$response->emit();
		}
	}
}

<?php
/**
 * Multi image class to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Image class to handle files.
 */
class Vtiger_MultiImage_File extends Vtiger_Basic_File
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
	 * Default image limit.
	 *
	 * @var int
	 */
	public static $defaultLimit = 10;

	/**
	 * View image.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function get(\App\Request $request)
	{
		if ($request->isEmpty('key', 2)) {
			throw new \App\Exceptions\NoPermitted('Not Acceptable', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$key = $request->getByType('key', 2);
		$value =  \App\Json::decode($recordModel->get($request->getByType('field', 2)));
		foreach ($value as $item) {
			if ($item['key'] === $key) {
				$file = \App\Fields\File::loadFromInfo([
					'path' => ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $item['path'],
					'name' => $item['name'],
				]);
				header('Pragma: public');
				header('Cache-Control: max-age=86400, public');
				header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
				header('Content-Type: ' . $file->getMimeType());
				header('Content-Transfer-Encoding: binary');
				header('Content-length: ' . $file->getSize());
				if ($request->getBoolean('download')) {
					header('Content-disposition: attachment; filename="' . $item['name'] . '"');
				}
				readfile($file->getPath());
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function post(\App\Request $request)
	{
		$attach = \App\Fields\File::uploadAndSave($request, $_FILES, $this->getFileType(), $this->getStorageName() . DIRECTORY_SEPARATOR . $request->getModule() . DIRECTORY_SEPARATOR . $request->getByType('field', 'Alnum'));
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult([
				'field' => $request->get('field'),
				'module' => $request->getModule(),
				'attach' => $attach,
			]);
			$response->emit();
		}
	}
}

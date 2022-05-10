<?php

/**
 * Backup download file action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Backup_DownloadFile_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		\CsrfMagic\Csrf::$rewrite = false;
		ob_implicit_flush();
		parent::__construct();
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($request->isEmpty('file')) {
			throw new \App\Exceptions\NoPermitted('ERR_FILE_EMPTY_NAME');
		}
		$requestFilePath = $request->getByType('file', \App\Purifier::PATH);
		$extension = explode('.', $requestFilePath);
		$extension = strtolower(array_pop($extension));
		if (!\in_array($extension, \App\Utils\Backup::getAllowedExtension())) {
			throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE');
		}
		$filePath = \App\Utils\Backup::getBackupCatalogPath() . DIRECTORY_SEPARATOR . $requestFilePath;
		if (!App\Fields\File::isAllowedFileDirectory($filePath)) {
			throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE');
		}
		header('content-description: File Transfer');
		header('content-type: application/octet-stream');
		header('content-disposition: attachment; filename=' . basename($filePath));
		header('content-transfer-encoding: binary');
		header('pragma: private');
		header('expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('cache-control: no-cache, must-revalidate');
		header('accept-ranges: bytes');
		header('content-length: ' . filesize($filePath));
		readfile($filePath);
	}
}

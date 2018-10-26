<?php

/**
 * Backup download file action class.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Backup_DownloadFile_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		if ($request->isEmpty('file')) {
			throw new \App\Exceptions\NoPermitted('ERR_FILE_EMPTY_NAME');
		}
		$requestFilePath = $request->getByType('file', 'Path');
		$extension = explode('.', $requestFilePath);
		$extension = strtolower(array_pop($extension));
		if (!\in_array($extension, \App\Utils\Backup::getAllowedExtension())) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED');
		}
		$filePath = \App\Utils\Backup::getBackupCatalogPath() . DIRECTORY_SEPARATOR . $requestFilePath;
		if (!App\Fields\File::isAllowedFileDirectory($filePath)) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED');
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($filePath));
		header('Content-Transfer-Encoding: binary');
		header('Pragma: private');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . filesize($filePath));
		readfile($filePath);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
	}
}

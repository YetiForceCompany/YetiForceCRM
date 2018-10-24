<?php

/**
 * Backup download file action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Backups_DownloadFile_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('download');
	}

	/**
	 * Download selected file.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function download(\App\Request $request)
	{
		$requestFilePath = $request->getByType('file', 'String');
		$filePath = Settings_Backups_Module_Model::getCatalogPath() . DIRECTORY_SEPARATOR . $requestFilePath;
		if (Settings_Backups_Module_Model::isAllowedFileDirectory($requestFilePath)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($filePath));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Accept-Ranges: bytes');
			header('Content-Length: ' . filesize($filePath));
			ob_clean();
			flush();
			readfile($filePath);
			exit;
		}
		throw new \App\Exceptions\NoPermittedForAdmin(\App\Language::translate('LBL_PERMISSION_DENIED'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess(true);
	}
}

<?php

/**
 * Mail download attachment action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_DownloadAttachment_Action extends Vtiger_Mass_Action
{
	/**
	 * Checking permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$id = $request->getInteger('record');
		$selectedFile = $request->getInteger('selectedFile');
		$filePath = Settings_Mail_Module_Model::getAttachmentPath($id, $selectedFile);
		if (file_exists($filePath)) {
			header('content-description: File Transfer');
			header('content-type: application/octet-stream');
			header('content-disposition: attachment; filename="' . basename($filePath) . '"');
			header('expires: 0');
			header('cache-control: must-revalidate');
			header('pragma: public');
			header('content-length: ' . filesize($filePath));
			readfile($filePath);
		}
	}
}

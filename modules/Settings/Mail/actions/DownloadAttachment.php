<?php

/**
 * Mail download attachment action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_DownloadAttachment_Action extends Vtiger_Mass_Action
{
	use \App\Controller\Traits\SettingsPermission;

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$id = $request->getInteger('record');
		$selectedFile = $request->getInteger('selectedFile');
		$fileInfo = Settings_Mail_Module_Model::getAttachmentInfo($id, $selectedFile);
		if ($fileInfo['path'] ?? null && file_exists($fileInfo['path'])) {
			header('content-description: File Transfer');
			header('content-type: application/octet-stream');
			header('content-disposition: attachment; filename="' . \App\Fields\File::sanitizeUploadFileName($fileInfo['name']) . '"');
			header('expires: 0');
			header('cache-control: must-revalidate');
			header('pragma: public');
			header('content-length: ' . filesize($fileInfo['path']));
			readfile($fileInfo['path']);
		}
	}
}

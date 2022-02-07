<?php
/**
 * DownloadFile class to handle files.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * DownloadFile class to handle files.
 */
class Documents_DownloadFile_File extends Vtiger_Basic_File
{
	/**
	 * Checking permission in get method.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function getCheckPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	/**
	 * Download file.
	 *
	 * @param \App\Request $request
	 *
	 * @return string|bool
	 */
	public function get(App\Request $request)
	{
		$documentRecordModel = Documents_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		//Download the file
		$documentRecordModel->set('show', $request->getBoolean('show'));
		$documentRecordModel->downloadFile();
		//Update the Download Count
		$documentRecordModel->updateDownloadCount();

		return false;
	}

	/**
	 * Api function to get file.
	 *
	 * @param App\Request $request
	 *
	 * @return \App\Fields\File
	 */
	public function api(App\Request $request): App\Fields\File
	{
		$documentRecordModel = Documents_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		//Download the file
		$documentRecordModel->set('return', true);
		$file = $documentRecordModel->downloadFile();
		//Update the Download Count
		$documentRecordModel->updateDownloadCount();
		return $file;
	}
}

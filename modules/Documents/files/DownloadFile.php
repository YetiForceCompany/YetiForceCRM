<?php
/**
 * DownloadFile class to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function getCheckPermission(\App\Request $request)
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
	public function get(\App\Request $request)
	{
		$documentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		//Download the file
		$documentRecordModel->set('show', $request->getBoolean('show'));
		$documentRecordModel->downloadFile();
		//Update the Download Count
		$documentRecordModel->updateDownloadCount();

		return false;
	}
}

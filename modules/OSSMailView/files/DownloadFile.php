<?php
/**
 * DownloadFile class to handle files.
 *
 * @package   File
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * DownloadFile class to handle files.
 */
class OSSMailView_DownloadFile_File extends Vtiger_Basic_File
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
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		if (!(new \App\Db\Query())->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $request->getInteger('record'), 'documentsid' => $request->getInteger('attachment')])->exists()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD');
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
		$documentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('attachment'), 'Documents');
		//Download the file
		$documentRecordModel->set('show', $request->getBoolean('show'));
		$documentRecordModel->downloadFile();
		//Update the Download Count
		$documentRecordModel->updateDownloadCount();

		return false;
	}
}

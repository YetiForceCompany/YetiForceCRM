<?php

/**
 * OSSMailView mbody view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Mbody_View extends Vtiger_Index_View
{
	use App\Controller\ClearProcess;

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	public function process(\App\Request $request)
	{
		\CsrfMagic\Csrf::$frameBreaker = false;
		\CsrfMagic\Csrf::$rewriteJs = null;
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('CONTENT', vtlib\Functions::getHtmlOrPlainText($recordModel->getDisplayValue('content')));
		$viewer->assign('RECORD', $record);
		$viewer->assign('SKIN_PATH', \Vtiger_Theme::getCurrentUserThemePath());
		$viewer->assign('LANGUAGE', \App\Language::getLanguage());
		$viewer->view('mbody.tpl', 'OSSMailView');
	}
}

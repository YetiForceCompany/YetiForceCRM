<?php

/**
 * Class to show hierarchy.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Class HelpDesk_Hierarchy_View.
 */
class HelpDesk_Hierarchy_View extends \App\Controller\View\Page
{
	use App\Controller\ClearProcess;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('STATUS_PICKLIST', $recordModel->getField('ticketstatus')->getPickListValues());
		$viewer->assign('HIERARCHY', $recordModel->getHierarchyDetails());
		$viewer->view('Hierarchy.tpl', $moduleName);
	}
}

<?php
/**
 * RecordPopover view Class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_RecordPopover_View.
 */
class Vtiger_RecordPopover_View extends \App\Controller\View\Page
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true) || !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordPopoverModel = Vtiger_RecordPopover_Model::getInstance($moduleName, $request->getInteger('record'));
		$viewer = $this->getViewer($request);
		$viewer->assign('HEADER_LINKS', $recordPopoverModel->getHeaderLinks());
		$viewer->assign('FIELDS_ICON', $recordPopoverModel->getFieldsIcon());
		$viewer->assign('RECORD', $recordPopoverModel->getRecord());
		$viewer->assign('FIELDS', $recordPopoverModel->getFields());
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('RecordPopover.tpl', $moduleName);
	}
}

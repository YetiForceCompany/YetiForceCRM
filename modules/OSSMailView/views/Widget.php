<?php

/**
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSMailView_Widget_View extends Vtiger_Edit_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!\App\Privilege::isPermitted($request->getByType('smodule'), 'DetailView', $request->getInteger('srecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$srecord = $request->getInteger('srecord');
		$smodule = $request->getByType('smodule');
		$type = $request->getByType('type', 2);
		$mode = $request->getMode();
		$record = $request->getInteger('record');
		$mailFilter = $request->getByType('mailFilter', 1);
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName)->setId($srecord);
		$config = OSSMail_Module_Model::getComposeParameters();
		if ($request->has('limit')) {
			$config['widget_limit'] = $request->getInteger('limit');
		}
		$relationModel = \Vtiger_Relation_Model::getInstanceById(\App\Relation::getRelationId($smodule, $moduleName))->set('parentRecord', $recordModel);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECOLDLIST', $recordModel->{$mode}($srecord, $smodule, $config, $type, $mailFilter));
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SMODULENAME', $smodule);
		$viewer->assign('RECORD', $record);
		$viewer->assign('SRECORD', $srecord);
		$viewer->assign('TYPE', $type);
		$viewer->assign('RELATION_MODEL', $relationModel);
		$viewer->assign('POPUP', $config['popup']);
		$viewer->view('widgets.tpl', 'OSSMailView');
	}
}

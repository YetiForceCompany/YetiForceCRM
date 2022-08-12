<?php

/**
 * Custom view advanced conditions modal view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Custom view advanced conditions modal view class.
 */
class Vtiger_CustomViewAdvCondModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_CUSTOM_VIEW_ADV_COND';

	/** {@inheritdoc} */
	public $successBtn = 'LBL_SAVE';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi-advenced-custom-view-conditions';

	/** @var array Advanced conditions */
	protected $advancedConditions;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule()) || !$currentUserPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CustomViewAdvCond')) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
		$this->advancedConditions = \App\Condition::validAdvancedConditions($request->getArray('advancedConditions'));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RELATIONS', Vtiger_Module_Model::getInstance($moduleName)->getRelations());
		$viewer->assign('ADVANCED_CONDITIONS', $this->advancedConditions);
		$viewer->view('CustomView/AdvCondModal.tpl', $moduleName);
	}
}

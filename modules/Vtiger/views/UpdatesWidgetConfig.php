<?php

/**
 * Update widget config modal view class.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_UpdatesWidgetConfig_View extends \App\Controller\Modal
{

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
		{
			$this->widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetId'), \App\User::getCurrentUserId());
			if (!\App\Privilege::isPermitted($request->getModule()) || $this->widget->get('userid') !== \App\User::getCurrentUserId()) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
	}

	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-lg';

	/**
	 * {@inheritdoc}
	 */
	public $showHeader = false;

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$widgetData = App\Json::decode($this->widget->get('data'));
		$viewer->assign('SELECTED_MODULES', $widgetData['selectedModules']);
		$viewer->assign('SELECTED_TRACKER_ACTIONS', $widgetData['selectedTrackerActions']);
		$viewer->assign('MODULES_LIST', Home_Module_Model::getModulesForUpdateWidgets());
		$viewer->view('Modals/UpdatesWidgetConfig.tpl', $moduleName);
	}
}

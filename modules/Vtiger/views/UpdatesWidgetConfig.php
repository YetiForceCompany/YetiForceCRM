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
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
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
		$query = new \App\Db\Query();
		$query->select(['vtiger_tab.*'])->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['<>', 'vtiger_tab.presence', 1]);
		$query->andWhere(['not in', 'vtiger_tab.name', ['Users']]);
		//'ModComments', 'PriceBooks', 'CallHistory', 'OSSMailView', 'SMSNotifier'
		$permittedModules = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!\App\Privilege::isPermitted($row['tabid'])) {
				$moduleModel = Vtiger_Module_Model::getInstanceFromArray($row);
				$permittedModules[$row['name']] = $moduleModel;
			}
		}
		$menu = Vtiger_Menu_Model::getAll(true);
		$permittedModulesTree = [];
		foreach ($menu as $parent) {
			if (!empty($parent['childs'])) {
				$items = [];
				foreach ($parent['childs'] as $child) {
					if (isset($permittedModules[$child['mod']])) {
						$items[$permittedModules[$child['mod']]->name] = $permittedModules[$child['mod']];
						unset($permittedModules[$child['mod']]);
					}
				}
				if (!empty($items)) {
					$permittedModulesTree[] = ['name' => $parent['name'], 'icon' => $parent['icon'], 'modules' => $items];
				}
			}
		}
		if (!empty($permittedModules)) {
			$permittedModulesTree[] = ['name' => 'LBL_OTHER', 'icon' => 'userIcon-Other', 'modules' => $permittedModules];
		}
		//	App\Cache::save('getQuickCreateModules', $restrictListString, $permittedModules);
		$widgetData = App\Json::decode(Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetId'), \App\User::getCurrentUserId())->get('data'));
		$viewer->assign('SELECTED_MODULES', $widgetData['selectedModules']);
		$viewer->assign('SELECTED_TRACKER_ACTIONS', $widgetData['selectedTrackerActions']);
		$viewer->assign('MODULES_LIST', $permittedModulesTree);
		$viewer->view('Modals/UpdatesWidgetConfig.tpl', $moduleName);
	}
}

<?php
/**
 * View to create chart with a filter.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * View to create chart with a filter.
 */
class Vtiger_ChartFilter_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_ADD_CHART_FILTER';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-chart-pie';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** @var \Vtiger_Widget_Model Widget Model */
	public $widgetModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$privilegesModel->hasModulePermission($request->getModule()) || !$privilegesModel->hasModulePermission($request->getModule(), 'CreateDashboardChartFilter')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('templateId', true) && \App\User::getCurrentUserModel()->isAdmin()) {
			$this->widgetModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('templateId'));
		} else {
			$linkData = \vtlib\Link::getLinkData($request->getInteger('linkId'));
			$this->widgetModel = \Vtiger_Widget_Model::getInstanceFromValues($linkData);
		}
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		if (!$this->widgetModel->getId()) {
			$this->pageTitle = \App\Language::translate($this->pageTitle, $request->getModule(), null, true, 'Dashboard');
		} else {
			$this->pageTitle = \App\Language::translate('LBL_EDIT_CHART_FILTER');
		}
		return $this->pageTitle;
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		if ('step1' === $request->getByType('step', \App\Purifier::ALNUM)) {
			parent::preProcessAjax($request);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$requiredFieldType = ['currency', 'currencyInventory', 'double', 'percentage', 'integer'];
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIZARD_STEP', $request->getByType('step', 'Alnum'));
		$viewer->assign('REQUIRED_FIELD_TYPE', $requiredFieldType);
		$viewer->assign('WIDGET_MODEL', $this->widgetModel);

		switch ($request->getByType('step', 'Alnum')) {
			case 'step1':
				$modules = vtlib\Functions::getAllModules(true, false, 0);
				$chartTypes = [
					'Pie' => 'LBL_PIE_CHART',
					'Donut' => 'LBL_DONUT_CHART',
					'Bar' => 'LBL_VERTICAL_BAR_CHART',
					'Horizontal' => 'LBL_HORIZONTAL_BAR_CHART',
					'Line' => 'LBL_LINE_CHART',
					'LinePlain' => 'LBL_LINE_CHART_PLAIN',
					'Funnel' => 'LBL_FUNNEL_CHART',
					'Table' => 'LBL_TABLE_CHART'
				];
				$viewer->assign('CHART_TYPES', $chartTypes);
				//Since comments is not treated as seperate module
				unset($modules['ModComments']);
				$viewer->assign('MODULES', $modules);
				break;
			case 'step2':
				$selectedModuleName = $request->getByType('selectedModule', 2);
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('ALLFILTERS', CustomView_Record_Model::getAllByGroup($selectedModuleName));
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				$viewer->assign('IS_NUMERAL_VALUE', !empty(\Vtiger_Module_Model::getInstance($selectedModuleName)->getFieldsByType($requiredFieldType, true)));
				break;
			case 'step3':
				$selectedModuleName = $request->getByType('selectedModule', 2);
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('MODULE_FIELDS', Vtiger_Module_Model::getInstance($selectedModuleName)->getFieldsByBlocks());
				$viewer->assign('VALUE_TYPE', $request->getByType('valueType'));
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				break;
			case 'step4':
				$selectedModuleName = $request->getByType('selectedModule', 2);
				$selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
				$groupFieldName = $request->getByType('groupField', \App\Purifier::ALNUM);
				$groupField = $selectedModuleModel->getFieldByName($groupFieldName);
				$viewer->assign('SHOW_GROUP_VALUES', \in_array($groupField->getFieldDataType(), ['date', 'datetime']));
				$viewer->assign('GROUP_VALUES', [
					'daily' => 'LBL_DAILY',
					'monthly' => 'LBL_MONTHLY',
					'yearly' => 'LBL_YEARLY'
				]);
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				$viewer->assign('SELECTED_MODULE_MODEL', $selectedModuleModel);
				$viewer->assign('MODULE_FIELDS', Vtiger_Module_Model::getInstance($selectedModuleName)->getFieldsByBlocks());
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('GROUP_FIELD', $groupFieldName);
				$viewer->assign('GROUP_FIELD_MODEL', $groupField);
				$filters = $request->getArray('filtersId', 'Integer');
				$viewer->assign('DIVIDING_FIELD', false);
				$viewer->assign('STACKED', false);
				$viewer->assign('SECTOR_FIELD', false);
				$viewer->assign('FILTERS', $filters);
				break;
			case 'step5':
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('DIVIDING_FIELD', $request->getByType('dividingField'));
				$sectorField = $request->isEmpty('sectorField') ? '' : $request->getByType('sectorField');
				$viewer->assign('SECTOR_FIELD', $sectorField);
				$viewer->assign('STACKED', $request->getBoolean('stacked', false));
				$viewer->assign('FILTERS', $request->getArray('filtersId', 'Integer'));
				break;
			default:
				break;
		}
		$viewer->view('dashboards/ChartFilter.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$viewName = $request->getByType('view', \App\Purifier::ALNUM);
		return $this->checkAndConvertJsScripts([
			"modules.Vtiger.resources.dashboards.{$viewName}",
			"modules.{$request->getModule()}.resources.dashboards.{$viewName}"
		]);
	}
}

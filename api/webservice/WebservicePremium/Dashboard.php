<?php
/**
 * Dashboard model file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium;

use App\Db\Query;
use App\Language;
use App\Module;

/**
 * Dashboard model class.
 */
class Dashboard
{
	/** @var string Module name. */
	protected $moduleName;

	/** @var int Type of dashboard. */
	protected $dashboardType;

	/** @var int Id application. */
	protected $application;

	/** @var string[] Id application. */
	public static $supportedWidgetsTypes = [
		'Mini List' => 'getMiniList',
		'ChartFilter' => 'getChartFilter',
	];

	/**
	 * Function to get instance.
	 *
	 * @param string $moduleName
	 * @param int    $dashboardType
	 * @param int    $application
	 *
	 * @return self
	 */
	public static function getInstance(string $moduleName, int $dashboardType, int $application): self
	{
		$instance = new static();
		$instance->moduleName = $moduleName;
		$instance->dashboardType = $dashboardType;
		$instance->application = $application;
		return $instance;
	}

	/**
	 * Set dashboard ID.
	 *
	 * @param int $dashboardType
	 *
	 * @return $this
	 */
	public function setDashboard(int $dashboardType): self
	{
		$this->dashboardType = $dashboardType;
		return $this;
	}

	/**
	 * Gets tabs.
	 *
	 * @return array
	 */
	public function getTabs(): array
	{
		$tabs = [];
		$dataReader = (new \App\Db\Query())->select(['u_#__dashboard_type.*'])->from('u_#__dashboard_type')
			->innerJoin('vtiger_module_dashboard_blocks', 'u_#__dashboard_type.dashboard_id = vtiger_module_dashboard_blocks.dashboard_id')
			->where(['vtiger_module_dashboard_blocks.authorized' => $this->application])
			->distinct()->createCommand()->query();
		while ($dashboard = $dataReader->read()) {
			$dbId = $dashboard['dashboard_id'];
			$tabs[$dbId] = [
				'name' => \App\Language::translate($dashboard['name'], $this->moduleName),
				'id' => $dbId,
				'system' => $dashboard['system'],
			];
		}
		return $tabs;
	}

	/**
	 * Return data about all added widgets in this dashboard.
	 *
	 * @return array
	 */
	public function getData(): array
	{
		$query = (new Query())->select(['vtiger_module_dashboard.*', 'vtiger_links.linklabel'])
			->from('vtiger_module_dashboard')
			->innerJoin('vtiger_module_dashboard_blocks', 'vtiger_module_dashboard_blocks.id = vtiger_module_dashboard.blockid')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard.linkid')
			->where([
				'vtiger_module_dashboard_blocks.dashboard_id' => $this->dashboardType,
				'vtiger_module_dashboard_blocks.tabid' => Module::getModuleId($this->moduleName),
				'vtiger_module_dashboard_blocks.authorized' => $this->application,
			]);
		$widgets = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['linkid'] = $row['id'];
			if (isset(self::$supportedWidgetsTypes[$row['linklabel']])) {
				$widgets[] = $this->{self::$supportedWidgetsTypes[$row['linklabel']]}($row);
			}
		}
		return $widgets;
	}

	/**
	 * Get mini list widget data.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function getMiniList(array $row): array
	{
		$widgetModel = new \Vtiger_MiniList_Model();
		$widgetModel->setWidgetModel(\Vtiger_Widget_Model::getInstanceFromValues($row));
		$headers = $records = [];
		$headerFields = $widgetModel->getHeaders();
		foreach ($headerFields as $fieldName => $fieldModel) {
			$headers[$fieldName] = Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
		}
		foreach ($widgetModel->getRecords('all') as $recordModel) {
			foreach ($headerFields as $fieldName => $fieldModel) {
				$records[$recordModel->getId()][$fieldName] = $recordModel->getDisplayValue($fieldName, $recordModel->getId(), true);
			}
		}
		return [
			'type' => $row['linklabel'],
			'data' => [
				'title' => \App\Language::translate($widgetModel->getTitle(), $widgetModel->getTargetModule()),
				'modulename' => $widgetModel->getTargetModule(),
				'headers' => $headers,
				'records' => $records,
			],
		];
	}

	/**
	 * Get chart filter widget data.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function getChartFilter(array $row): array
	{
		$widgetModel = new \Vtiger_ChartFilter_Model();
		$widgetModel->setWidgetModel(\Vtiger_Widget_Model::getInstanceFromValues($row));
		return [
			'type' => $row['linklabel'],
			'data' => [
				'title' => $widgetModel->getTitle(),
				'modulename' => $widgetModel->getTargetModule(),
				'stacked' => $widgetModel->isStacked() ? 1 : 0,
				'colorsFromDividingField' => $widgetModel->areColorsFromDividingField() ? 1 : 0,
				'filterIds' => $widgetModel->getFilterIds(),
				'typeChart' => $widgetModel->getType(),
				'widgetData' => $widgetModel->getChartData(),
			],
		];
	}
}

<?php
/**
 * Dashboard class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal;

use App\Db\Query;
use App\Language;
use App\Module;

/**
 * Model dashboard.
 */
class Dashboard
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName;

	/**
	 * Type of dashboard.
	 *
	 * @var int
	 */
	public $dashboardType;

	/**
	 * Id application.
	 *
	 * @var int
	 */
	public $application;

	/**
	 * Function to get instance.
	 *
	 * @param string $moduleName
	 * @param int    $dashboardType
	 *
	 * @return void
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
	 * Return data about all added widgets in this dashboard.
	 *
	 * @return array
	 */
	public function getData()
	{
		$dataReader = (new Query())->select(['vtiger_module_dashboard.*', 'vtiger_links.linklabel'])
			->from('vtiger_module_dashboard')
			->innerJoin('vtiger_module_dashboard_blocks', 'vtiger_module_dashboard_blocks.id = vtiger_module_dashboard.blockid')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard.linkid')
			->where([
				'vtiger_module_dashboard_blocks.dashboard_id' => $this->dashboardType,
				'vtiger_module_dashboard_blocks.tabid' => Module::getModuleId($this->moduleName),
				'vtiger_module_dashboard_blocks.authorized' => $this->application,
			])
			->createCommand()->query();
		$widgets = [];
		while ($row = $dataReader->read()) {
			$row['linkid'] = $row['id'];
			if ('Mini List' === $row['linklabel']) {
				$minilistWidget = \Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new \Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$headers = $records = [];
				$headerFields = $minilistWidgetModel->getHeaders();
				foreach ($headerFields as $fieldName => $fieldModel) {
					$headers[$fieldName] = Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
				}
				foreach ($minilistWidgetModel->getRecords(null) as $recordModel) {
					foreach ($headerFields as $fieldName => $fieldModel) {
						$records[$recordModel->getId()][$fieldName] = $recordModel->getListViewDisplayValue($fieldModel);
					}
				}
				$widgets[] = [
					'type' => $row['linklabel'],
					'data' => [
						'title' => $minilistWidgetModel->getTitle(),
						'modulename' => $minilistWidgetModel->getTargetModuleModel()->getName(),
						'headers' => $headers,
						'records' => $records
					]
				];
			}
		}
		return $widgets;
	}
}

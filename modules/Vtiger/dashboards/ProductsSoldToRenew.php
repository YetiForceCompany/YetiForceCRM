<?php

/**
 * ProductsSoldToRenew Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ProductsSoldToRenew_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request, $widget = null)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());
		$this->setWidgetModel($widget);
		$this->setData([
			'orderby' => $request->getForSql('orderby'),
			'sortorder' => $request->getForSql('sortorder'),
		]);
		$data['orderby'] = $this->getFromData('orderby');
		$data['sortorder'] = $this->getFromData('sortorder');
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('OWNER', $currentUser->getId());
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('WIDGET_MODEL', $this);
		$viewer->assign('BASE_MODULE', $this->getTargetModule());
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/ProductsSoldToRenewContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ProductsSoldToRenew.tpl', $moduleName);
		}
	}

	public function setData($data)
	{
		if (empty($data['orderby'])) {
			$data['orderby'] = 'dateinservice';
			$data['sortorder'] = 'asc';
		}
		return $this->data = $data;
	}

	public function getFromData($key)
	{
		return $this->data[$key];
	}

	public function setWidgetModel($widgetModel)
	{
		$this->widgetModel = $widgetModel;
	}

	public function getTargetModuleModel()
	{
		if (empty($this->targetModuleModel)) {
			$this->targetModuleModel = Vtiger_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	public function getRecordLimit()
	{
		$limit = 10;
		if ($this->widgetModel->get('limit')) {
			$limit = $this->widgetModel->get('limit');
		}
		return $limit;
	}

	public function getTargetModule()
	{
		return 'Assets';
	}

	public function getTargetFields()
	{
		return ['id', 'assetname', 'parent_id', 'dateinservice'];
	}

	public function getRestrictFields()
	{
		return [];
	}

	protected function initListViewController()
	{
		if (empty($this->queryGenerator)) {
			$this->queryGenerator = new \App\QueryGenerator($this->getTargetModule());
			$this->queryGenerator->setFields($this->getTargetFields());
			$this->listviewHeaders = $this->listviewRecords = null;
		}
	}

	public function getHeaders()
	{
		$this->initListViewController();
		if (empty($this->listviewHeaders)) {
			$headerFieldModels = [];
			foreach ($this->queryGenerator->getListViewFields() as $fieldName => &$fieldsModel) {
				if (\in_array($fieldName, $this->getRestrictFields())) {
					continue;
				}
				$headerFieldModels[$fieldName] = $fieldsModel;
			}
			$this->listviewHeaders = $headerFieldModels;
		}
		return $this->listviewHeaders;
	}

	public function getHeaderCount()
	{
		return \count($this->getHeaders());
	}

	/**
	 * Gets record list.
	 *
	 * @return array
	 */
	public function getRecords(): array
	{
		$this->initListViewController();
		if (empty($this->listviewRecords)) {
			foreach ($this->getConditions() as $condition) {
				$this->queryGenerator->addCondition($condition[0], $condition[2], $condition[1]);
			}
			$this->queryGenerator->setOrder($this->getFromData('orderby'), $this->getFromData('sortorder'));
			$query = $this->queryGenerator->createQuery();
			$query->limit($this->getRecordLimit());

			$this->listviewRecords = [];
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$this->listviewRecords[$row['id']] = $this->getTargetModuleModel()->getRecordFromArray($row);
			}
			$dataReader->close();
		}
		return $this->listviewRecords;
	}

	public function getFieldNameToSecondButton()
	{
		return 'assets_renew';
	}

	/**
	 * Conditions.
	 *
	 * @return array
	 */
	public function getConditions(): array
	{
		return [['assetstatus', 'e', 'PLL_ACCEPTED'], ['assets_renew', 'e', 'PLL_WAITING_FOR_RENEWAL']];
	}

	/**
	 * Gets url.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->getTargetModuleModel()->getListViewUrl() . '&viewname=All&search_params=' . urlencode(App\Json::encode([$this->getConditions()]));
	}
}

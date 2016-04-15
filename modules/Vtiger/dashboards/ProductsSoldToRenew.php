<?php

/**
 * ProductsSoldToRenew Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ProductsSoldToRenew_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request, $widget = NULL)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}

		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());

		$this->setWidgetModel($widget);
		$this->setData($data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('OWNER', $currentUser->getId());
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('WIDGET_MODEL', $this);
		$viewer->assign('BASE_MODULE', $this->getTargetModule());
		$viewer->assign('LISTVIEWLINKS', true);
		$viewer->assign('DATA', $data);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/ProductsSoldToRenewContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ProductsSoldToRenew.tpl', $moduleName);
		}
	}

	public function setData($data)
	{
		if (empty($data['sortFieldName'])) {
			$data['sortFieldName'] = 'vtiger_assets.assetname';
			$data['sortorder'] = 'asc';
		}
		$this->data = $data;
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
		if (!$this->targetModuleModel) {
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

	protected function initListViewController()
	{
		if (!$this->listviewController) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$db = PearDatabase::getInstance();

			$this->queryGenerator = new QueryGenerator($this->getTargetModule(), $currentUserModel);
			$this->queryGenerator->setFields($this->getTargetFields());
			$this->listviewController = new ListViewController($db, $currentUserModel, $this->queryGenerator);
			$this->listviewHeaders = $this->listviewRecords = NULL;
		}
	}

	public function getHeaders()
	{
		$this->initListViewController();

		if (!$this->listviewHeaders) {
			$headerFieldModels = [];
			foreach ($this->listviewController->getListViewHeaderFields() as $fieldName => $webserviceField) {
				$fieldObj = Vtiger_Field::getInstance($webserviceField->getFieldId());
				$headerFieldModels[$fieldName] = Vtiger_Field_Model::getInstanceFromFieldObject($fieldObj);
			}
			$this->listviewHeaders = $headerFieldModels;
		}

		return $this->listviewHeaders;
	}

	public function getHeaderCount()
	{
		return count($this->getHeaders());
	}

	public function getRecords($user)
	{

		$this->initListViewController();
		if (!$this->listviewRecords) {
			$db = PearDatabase::getInstance();
			$status = ['PLL_TO_RENEW', 'PLL_SOLD'];
			$where = ' AND assetstatus IN (' . $db->generateQuestionMarks($status) . ') ';

			$query = $this->queryGenerator->getQuery() . $where;
			$query .= ' ORDER BY ' . $this->getFromData('sortFieldName') . ' ' . $this->getFromData('sortorder');
			$query .= ' LIMIT 0,' . $this->getRecordLimit();
			$result = $db->pquery($query, $status);

			$targetModuleName = $this->getTargetModule();
			$targetModuleFocus = CRMEntity::getInstance($targetModuleName);

			$entries = $this->listviewController->getListViewRecords($targetModuleFocus, $targetModuleName, $result);

			$this->listviewRecords = [];
			foreach ($entries as $id => $record) {
				$rawData = $db->getRow($result);
				$record['id'] = $id;
				$this->listviewRecords[$id] = $this->getTargetModuleModel()->getRecordFromArray($record, $rawData);
			}
		}

		return $this->listviewRecords;
	}
}

<?php

/**
 * Upcoming events dashboard file.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Upcoming events dashboard class.
 */
class Vtiger_UpcomingEvents_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * @var bool Skip the year
	 */
	private $skipYear;
	/**
	 * @var Vtiger_Field_Model Filter field model
	 */
	private $fieldModel;

	/** {@inheritdoc}  */
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetid'), \App\User::getCurrentUserId());
		$this->parseDataWidget(\App\Json::decode(App\Purifier::decodeHtml($widget->get('data'))));
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		if (isset($this->fieldModel)) {
			$viewer->assign('FIELD_NAME', App\Language::translate($this->fieldModel->getFieldLabel(), $this->fieldModel->getModuleName()));
			$viewer->assign('RECORDS', $this->getWidgetData($request));
		}
		if ($request->has('content')) {
			$viewer->view('dashboards/UpcomingEventsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/UpcomingEvents.tpl', $moduleName);
		}
	}

	/**
	 * Parse widget data.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function parseDataWidget(array $data): void
	{
		$this->skipYear = !empty($data['skip_year']);
		$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($data['date_fields']);
		if ($fieldModel->isActiveField()) {
			$this->fieldModel = $fieldModel;
		}
	}

	/**
	 * Widget data.
	 *
	 * @return array
	 */
	private function getWidgetData(): array
	{
		$moduleName = $this->fieldModel->getModuleName();
		$fieldName = $this->fieldModel->getName();
		$columnName = $this->fieldModel->getColumnName();
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', $fieldName]);
		$queryGenerator->addCondition($fieldName, '', 'ny');
		$query = $queryGenerator->createQuery();
		if ($this->skipYear) {
			$now = date('m, d');
			$query->andWhere(new \yii\db\Expression("(EXTRACT(MONTH FROM $columnName), EXTRACT(DAY FROM $columnName)) >= ($now)"));
			$query->orderBy(new \yii\db\Expression("EXTRACT(MONTH FROM $columnName), EXTRACT(DAY FROM $columnName)"));
		} else {
			$query->orderBy([$columnName => SORT_ASC]);
		}
		$uiTypeModel = $this->fieldModel->getUITypeModel();
		$dataReader = $query->createCommand()->query();
		$records = [];
		while ($row = $dataReader->read()) {
			$val = $uiTypeModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
			if ($this->skipYear) {
				[, $m, $d] = App\Fields\Date::explode($row[$fieldName]);
				$val = '<span title="' . $val . '">' . \Vtiger_Util_Helper::formatDateDiffInStrings(date('Y') . "-$m-$d") . '</span>';
			}
			$records[] = [
				'id' => $row['id'],
				'value' => $val,
				'name' => App\Record::getLabel($row['id']),
				'url' => "index.php?module={$moduleName}&view=Detail&record={$row['id']}",
			];
		}
		return $records;
	}
}

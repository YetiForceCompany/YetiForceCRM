<?php

/**
 * QuickEdit view for module Calendar.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Calendar_QuickEditAjax_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->isEmpty('record', true)) {
			if (!\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$recordStructure = $recordStructureInstance->getStructure();
		$fieldValues = [];
		$fieldList = $recordModel->getModule()->getFields();
		$sourceRelatedField = $recordModel->getModule()->getValuesFromSource($request);
		foreach ($sourceRelatedField as $fieldName => &$fieldValue) {
			if (isset($recordStructure[$fieldName])) {
				$fieldvalue = $recordStructure[$fieldName]->get('fieldvalue');
				if (empty($fieldvalue)) {
					$recordStructure[$fieldName]->set('fieldvalue', $fieldValue);
				}
			} elseif (isset($fieldList[$fieldName])) {
				$fieldModel = $fieldList[$fieldName];
				$fieldModel->set('fieldvalue', $fieldValue);
				$fieldValues[$fieldName] = $fieldModel;
			}
		}
		$viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('QUICKCREATE_LINKS', Vtiger_QuickCreateView_Model::getInstance($moduleName)->getLinks([]));
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode(
			\App\Fields\Picklist::getPicklistDependencyDatasource($moduleName))
		);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(
			\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName))
		);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('SOURCE_RELATED_FIELD', $fieldValues);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
		$viewer->assign('IS_POSTPONED', $request->getBoolean('isDuplicate'));
		$viewer->assign('EVENT_LIMIT', AppConfig::module('Calendar', 'EVENT_LIMIT'));
		$viewer->assign('WEEK_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'agendaWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'agendaDay' : 'basicDay');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_ID', $recordModel->getId());
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('VIEW', $request->getByType('view'));
		$tplName = AppConfig::module('Calendar', 'CALENDAR_VIEW') . DIRECTORY_SEPARATOR . 'QuickEdit.tpl';
		$viewer->view($tplName, $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFiles = parent::getFooterScripts($request);
		if (AppConfig::module('Calendar', 'CALENDAR_VIEW') === 'Extended') {
			$jsFiles = array_merge($jsFiles, $this->checkAndConvertJsScripts([
				'~libraries/moment/min/moment.min.js',
				'~libraries/fullcalendar/dist/fullcalendar.js',
				'~libraries/css-element-queries/src/ResizeSensor.js',
				'~libraries/css-element-queries/src/ElementQueries.js',
				'~layouts/resources/Calendar.js',
				'modules.Calendar.resources.Standard.CalendarView',
				'modules.Calendar.resources.Extended.YearView',
				'modules.Calendar.resources.Extended.CalendarView',
				'modules.Calendar.resources.ActivityStateModal'
			]));
		}
		return $jsFiles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return $this->checkAndConvertCssStyles([
			'~libraries/fullcalendar/dist/fullcalendar.css',
		]);
	}
}

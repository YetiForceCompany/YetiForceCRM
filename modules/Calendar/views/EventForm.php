<?php

/**
 * Calendar EventForm View class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Calendar_EventForm_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('record')) {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			if (!$this->record->isEditable() ||
				($request->getBoolean('isDuplicate') === true && (!$this->record->isCreateable() || !$this->record->isPermitted('ActivityPostponed')))
			) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			parent::checkPermission($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		if ($request->has('record')) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
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
			$viewer->assign('IS_POSTPONED', $request->getBoolean('isDuplicate'));
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('RECORD_ID', $request->getBoolean('isDuplicate') ? '' : $recordModel->getId());
			$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
			$viewer->assign('VIEW', $request->getByType('view'));
		} else {
			parent::process($request);
			$viewer->assign('RECORD', null);
			$viewer->assign('RECORD_ID', '');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Extended/EventForm.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFiles = parent::getFooterScripts($request);
		if (!empty(AppConfig::module('Calendar', 'SHOW_ACTIVITY_BUTTONS_IN_EDIT_FORM'))) {
			$jsFiles = array_merge($jsFiles, $this->checkAndConvertJsScripts([
				'modules.Calendar.resources.ActivityStateModal'
			]));
		}
		return $jsFiles;
	}
}

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
class Calendar_QuickEditAjax_View extends Calendar_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		$isPermited = false;
		$moduleName = $request->getModule();
		if ($request->has('record')) {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			$isPermited = $this->record->isEditable();
		}
		if (!$isPermited) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		parent::process($request);
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
		$viewer->assign('SOURCE_RELATED_FIELD', $fieldValues);
		$viewer->assign('RECORD_ID', $recordModel->getId());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFiles = parent::getFooterScripts($request);
		if (AppConfig::module('Calendar', 'CALENDAR_VIEW') === 'Extended') {
			$jsFiles = array_merge($jsFiles, $this->checkAndConvertJsScripts([
				'modules.Calendar.resources.ActivityStateModal'
			]));
		}
		return $jsFiles;
	}
}

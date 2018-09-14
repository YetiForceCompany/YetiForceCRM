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
 */
class Calendar_EventForm_View extends Vtiger_QuickCreateAjax_View
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('record')) {
			$requestRecordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		}

		$module = 'Calendar';
		$quickCreateContents = [];
		$info = [];
		if (!empty($requestRecordModel) && $module == $requestRecordModel->getModuleName()) {
			$recordModel = $requestRecordModel;
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($module);
		}
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		foreach (array_intersect($request->getKeys(), array_keys($fieldList)) as $fieldName) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel);
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$recordStructure = $recordStructureInstance->getStructure();
		uksort($recordStructure, function ($a, $b) use ($recordStructure) {
			$types = ['string', 'datetime', 'date', 'time', 'owner', 'sharedOwner', 'picklist', 'referenceLink', 'referenceProcess', 'referenceSubProcess', 'reference'];
			if (($val1 = array_search($recordStructure[$a]->getFieldDataType(), $types)) === false) {
				$val1 = 99;
			}
			if (($val2 = array_search($recordStructure[$b]->getFieldDataType(), $types)) === false) {
				$val2 = 99;
			}
			return strcasecmp($val1, $val2);
		});
		$fieldValues = [];
		$sourceRelatedField = $moduleModel->getValuesFromSource($request);
		foreach ($sourceRelatedField as $fieldName => $fieldValue) {
			if (isset($recordStructure[$fieldName])) {
				$fieldvalue = $recordStructure[$fieldName]->get('fieldvalue');
				if (empty($fieldvalue)) {
					$recordStructure[$fieldName]->set('fieldvalue', $fieldValue);
				}
			} else {
				if (isset($fieldList[$fieldName])) {
					$fieldModel = $fieldList[$fieldName];
					$fieldModel->set('fieldvalue', $fieldValue);
					$fieldValues[$fieldName] = $fieldModel;
				}
			}
		}
		$info['recordStructureModel'] = $recordStructureInstance;
		$info['recordStructure'] = $recordStructure;
		$info['moduleModel'] = $moduleModel;
		$quickCreateContents[$module] = $info;
		$picklistDependencyDatasource = \App\Fields\Picklist::getPicklistDependencyDatasource($moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('QUICKCREATE_LINKS', Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['QUICKCREATE_VIEW_HEADER']));
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode($picklistDependencyDatasource));
		$mappingRelatedField = \App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode($mappingRelatedField));
		$viewer->assign('SOURCE_RELATED_FIELD', $fieldValues);
		$viewer->assign('THREEDAYSAGO', date('Y-n-j', strtotime('-3 day')));
		$viewer->assign('TWODAYSAGO', date('Y-n-j', strtotime('-2 day')));
		$viewer->assign('ONEDAYAGO', date('Y-n-j', strtotime('yesterday')));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('ONEDAYLATER', date('Y-n-j', strtotime('tomorrow')));
		$viewer->assign('TWODAYLATER', date('Y-n-j', strtotime('+2 day')));
		$viewer->assign('THREEDAYSLATER', date('Y-n-j', strtotime('+3 day')));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUICK_CREATE_CONTENTS', $quickCreateContents);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('VIEW', $request->getByType('view'));
		$viewer->view('Extended/EventForm.tpl', $moduleName);
	}
}

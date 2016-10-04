<?php

/**
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSPasswords_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();

		// get min, max, allow_chars from vtiger_passwords_config
		$result = $adb->query("SELECT * FROM vtiger_passwords_config WHERE 1 LIMIT 1", true);
		$min = $adb->query_result($result, 0, 'pass_length_min');
		$max = $adb->query_result($result, 0, 'pass_length_max');
		$allowChars = $adb->query_result($result, 0, 'pass_allow_chars');

		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$moduleModel = $recordModel->getModule();

		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isEditable()) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$recordStructure = $recordStructureInstance->getStructure();
		$sourceRelatedField = $moduleModel->getValuesFromSource($request);
		foreach ($sourceRelatedField as $field => $value) {
			if (isset($recordStructure[$field])) {
				$fieldvalue = $recordStructure[$field]->get('fieldvalue');
				if (empty($fieldvalue)) {
					$recordStructure[$field]->set('fieldvalue', $value);
					unset($sourceRelatedField[$field]);
				}
			}
		}

		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$relatedModule = 'OSSPasswords';

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATEDMODULE', $relatedModule);
		$viewer->assign('GENERATEPASS', 'Generate Password');
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('VALIDATE_STRINGS', vtranslate('Very Weak', $relatedModule) . ',' . vtranslate('Weak', $relatedModule) . ',' . vtranslate('Better', $relatedModule) . ',' .
			vtranslate('Medium', $relatedModule) . ',' . vtranslate('Strong', $relatedModule) . ',' . vtranslate('Very Strong', $relatedModule));
		$viewer->assign('Very Weak', 'Very Weak');
		$viewer->assign('Weak', 'Weak');
		$viewer->assign('Better', 'Better');
		$viewer->assign('Medium', 'Medium');
		$viewer->assign('Strong', 'Strong');
		$viewer->assign('Very Strong', 'Very Strong');
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \includes\utils\Json::encode($picklistDependencyDatasource));
		$mappingRelatedField = Vtiger_ModulesHierarchy_Model::getRelationFieldByHierarchy($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD', \includes\utils\Json::encode($mappingRelatedField));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('passLengthMin', $min);
		$viewer->assign('passLengthMax', $max);
		$viewer->assign('allowChars', $allowChars);
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));

		echo $viewer->view('QuickCreate.tpl', $moduleName, true);
	}
}

<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSPasswords_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	public function process(\App\Request $request)
	{
		// get min, max, allow_chars from vtiger_passwords_config
		$passwordConfig = (new App\Db\Query())->from('vtiger_passwords_config')->one();

		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
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

		$picklistDependencyDatasource = \App\Fields\Picklist::getPicklistDependencyDatasource($moduleName);
		$relatedModule = 'OSSPasswords';

		$viewer = $this->getViewer($request);
		$viewer->assign('QUICKCREATE_LINKS', Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['QUICKCREATE_VIEW_HEADER']));
		$viewer->assign('RELATEDMODULE', $relatedModule);
		$viewer->assign('GENERATEPASS', 'Generate Password');
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('VALIDATE_STRINGS', \App\Language::translate('Very Weak', $relatedModule) . ',' . \App\Language::translate('Weak', $relatedModule) . ',' . \App\Language::translate('Better', $relatedModule) . ',' .
			\App\Language::translate('Medium', $relatedModule) . ',' . \App\Language::translate('Strong', $relatedModule) . ',' . \App\Language::translate('Very Strong', $relatedModule));
		$viewer->assign('Very Weak', 'Very Weak');
		$viewer->assign('Weak', 'Weak');
		$viewer->assign('Better', 'Better');
		$viewer->assign('Medium', 'Medium');
		$viewer->assign('Strong', 'Strong');
		$viewer->assign('Very Strong', 'Very Strong');
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode($picklistDependencyDatasource));
		$mappingRelatedField = \App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode($mappingRelatedField));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('passLengthMin', $passwordConfig['pass_length_min']);
		$viewer->assign('passLengthMax', $passwordConfig['pass_length_max']);
		$viewer->assign('allowChars', $passwordConfig['pass_allow_chars']);
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));

		echo $viewer->view('QuickCreate.tpl', $moduleName, true);
	}
}

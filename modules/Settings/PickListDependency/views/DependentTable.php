<?php

class Settings_PickListDependency_DependentTable_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\Traits\SettingsPermission;

	public function process(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$module = $request->getByType('sourceModule', \App\Purifier::STANDARD);
		$sourceField = $request->getByType('sourceField', \App\Purifier::ALNUM);
		$secondField = $request->getByType('secondField', \App\Purifier::ALNUM);
		$thirdField = $request->getByType('thirdField', \App\Purifier::ALNUM);
		$recordModel = Settings_PickListDependency_Record_Model::getInstance($module, $sourceField, $secondField, $thirdField);
		$valueMapping = $recordModel->getPickListDependency();
		$nonMappedSourceValues = $recordModel->getNonMappedSourcePickListValues();

		//	echo '<pre>';
		//	var_dump($recordModel);
		$viewer = $this->getViewer($request);
		$viewer->assign('MAPPED_VALUES', $valueMapping);
		$viewer->assign('SOURCE_PICKLIST_VALUES', $recordModel->getSourcePickListValues());
		$viewer->assign('TARGET_PICKLIST_VALUES', $recordModel->getTargetPickListValues());
		$viewer->assign('THIRD_FIELD_PICKLIST_VALUES', $recordModel->getPickListValuesForField());
		$viewer->assign('NON_MAPPED_SOURCE_VALUES', $nonMappedSourceValues);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('RECORD_MODEL', $recordModel);

		$viewer->view('DependentTable.tpl', $qualifiedName);
	}
}

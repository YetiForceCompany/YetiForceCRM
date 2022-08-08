<?php

/**
 * Settings TreesManager save action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Save tree.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record', true)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($request->getInteger('record'));
			$recordModel->set('replace', $request->getMultiDimensionArray('replace', [['old' => ['Integer'], ['new' => ['Integer']]]]));
		} else {
			$recordModel = Settings_TreesManager_Record_Model::getCleanInstance($qualifiedModuleName);
		}

		$fields = $recordModel->editFields;
		$fields['tree'] = 'tree';
		foreach ($fields as $fieldName) {
			if ($request->has($fieldName)) {
				$fieldModel = $recordModel->getFieldInstanceByName($fieldName);
				if ($fieldModel->isEditableReadOnly()) {
					continue;
				}
				switch ($fieldName) {
					case 'share':
						$value = $request->getArray($fieldName, \App\Purifier::INTEGER);
						$value = array_intersect($value, array_keys($fieldModel->getPicklistValues()));
						break;
					case 'tree':
						$tree = $request->getArray('tree', 'Text');
						$value = $recordModel->parseTreeDataForSave($tree);
						break;
					default:
						$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
						$fieldUITypeModel = $fieldModel->getUITypeModel();
						$fieldUITypeModel->validate($value, true);
						$value = $fieldModel->getDBValue($value);
						break;
				}
				$recordModel->set($fieldName, $value);
			}
		}
		$recordModel->save();

		header('location: ' . $recordModel->getModule()->getListViewUrl());
	}
}

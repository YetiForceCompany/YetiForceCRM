<?php
/**
 * KnowledgeBase Save to database 
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */

class KnowledgeBase_Save_Action extends Vtiger_Save_Action
{
	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if ($fieldName === 'content') {
				// TODO filter through special method which deletes scripts and css
				$fieldValue = $request->getRaw($fieldName);
			} else if ($request->has($fieldName)) {
				$fieldValue = $request->get($fieldName, null);
			} else if ($fieldModel->getDisplayType() == 5) {
				$fieldValue = $recordModel->get($fieldName);
			} else {
				$fieldValue = $fieldModel->getDefaultFieldValue();
			}
			$fieldDataType = $fieldModel->getFieldDataType();
			if ($fieldDataType == 'time') {
				$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
			}
			if ($fieldValue !== null) {
				if (!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			} else
				$recordModel->set($fieldName, null);
		}
		return $recordModel;
	}
}


<?php
/**
 * Save action class for KnowledgeBase 
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */

class KnowledgeBase_Save_Action extends Vtiger_Save_Action
{

	protected function getRecordModelFromRequest(Vtiger_Request $request)
	{

		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if (!empty($recordId)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');
		}

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if ($request->has($fieldName)) {
				$fieldValue = $request->getForHtml($fieldName, null);
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

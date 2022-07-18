<?php
/**
 * UIType Phone Field file.
 *
 * @package UiType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Phone UIType class.
 */
class SMSNotifier_Phone_UIType extends Vtiger_Phone_UIType
{
	/**
	 * Gets phones from related record.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getRelatedFields(Vtiger_Record_Model $recordModel): array
	{
		$phones = [];
		$relatedId = $recordModel->get('related_to') ?: $recordModel->getField('related_to')->get('fieldvalue');
		$fieldModel = $this->getFieldModel();
		if ($relatedId && \App\Record::isExists($relatedId)) {
			$relatedRecordModel = Vtiger_Record_Model::getInstanceById($relatedId);
			$sourceModuleModel = $relatedRecordModel->getModule();
			$refField = $recordModel->getField('related_to');

			if ($refField && $refField->isActiveField() && $fieldModel && $fieldModel->isActiveField() && \in_array($relatedRecordModel->getModuleName(), $refField->getReferenceList())) {
				foreach ($sourceModuleModel->getFieldsByType('phone', true) as $phoneModel) {
					if (!$relatedRecordModel->isEmpty($phoneModel->getName()) && $phoneModel->isViewable()) {
						$phoneField = clone $phoneModel;
						$phoneField->set('fieldvalue', $relatedRecordModel->get($phoneModel->getName()));
						$phones[$phoneField->getName()] = $phoneField;
					}
				}
			}
		}

		return $phones;
	}
}

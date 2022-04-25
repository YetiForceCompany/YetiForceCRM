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
	 * @param int $relatedId
	 *
	 * @return array
	 */
	public function getRelatedFields(int $relatedId): array
	{
		$phones = [];
		$fieldModel = $this->getFieldModel();
		if (\App\Record::isExists($relatedId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($relatedId);
			$sourceModuleModel = $recordModel->getModule();
			$refField = $fieldModel->getModule()->getFieldByName('related_to');

			if ($refField && $refField->isActiveField() && $fieldModel && $fieldModel->isActiveField() && \in_array($recordModel->getModuleName(), $refField->getReferenceList())) {
				foreach ($sourceModuleModel->getFieldsByType('phone') as $phoneModel) {
					if (!$recordModel->isEmpty($phoneModel->getName()) && $phoneModel->isViewable()) {
						$phoneField = clone $phoneModel;
						$phoneField->set('fieldvalue', $recordModel->get($phoneModel->getName()));
						$phones[$phoneField->getName()] = $phoneField;
					}
				}
			}
		}

		return $phones;
	}
}

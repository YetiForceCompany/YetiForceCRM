<?php
/**
 * UIType String Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class ModComments_String_UIType extends Vtiger_Text_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		if ($fieldName === 'parents') {
			if (!$request->isEmpty('parent_comments')) {
				$parentModel = Vtiger_Record_Model::getInstanceById($request->getInteger('parent_comments'));
				if (!empty($parentModel->get('parents'))) {
					$parents = implode('::', [$parentModel->get('parents'), $parentModel->get('modcommentsid')]);
				} else {
					$parents = $parentModel->get('modcommentsid');
				}
			}
			$recordModel->set('parents', $this->getDBValue($parents ?? null, $recordModel));
		} else {
			$value = $request->getByType($requestFieldName, 2);
			$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
		}
	}
}

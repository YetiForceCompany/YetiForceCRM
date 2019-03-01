<?php
/**
 * UIType Picklist Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * User Picklist Field Class.
 */
class Users_Picklist_UIType extends Vtiger_Picklist_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$currentModel = \App\User::getCurrentUserModel();
		if ($this->getFieldModel()->getUIType() === 115 && (!$currentModel->isAdmin() || $currentModel->getId() === $recordModel->getId())) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName(), 406);
		}
		parent::setValueFromRequest($request, $recordModel, $requestFieldName);
	}
}

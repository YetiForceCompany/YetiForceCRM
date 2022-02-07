<?php
/**
 * UIType Picklist Field Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * User Picklist Field Class.
 */
class Users_Picklist_UIType extends Vtiger_Picklist_UIType
{
	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$currentModel = \App\User::getCurrentUserModel();
		if (115 === $this->getFieldModel()->getUIType() && (!$currentModel->isAdmin() || $currentModel->getId() === $recordModel->getId())) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName(), 406);
		}
		parent::setValueFromRequest($request, $recordModel, $requestFieldName);
	}
}

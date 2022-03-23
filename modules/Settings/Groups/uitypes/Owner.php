<?php
/**
 * UIType owner field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Owner Field Class.
 */
class Settings_Groups_Owner_UIType extends Vtiger_Owner_UIType
{
	/**
	 * Gets owner list.
	 *
	 * @param Settings_Groups_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getOwnerList(?Settings_Groups_Record_Model $recordModel = null): array
	{
		$ownerInstance = \App\Fields\Owner::getInstance();
		$owners['LBL_USERS'] = $ownerInstance->getAccessibleUsers('', $this->getFieldModel()->getFieldDataType());
		$owners['LBL_GROUPS'] = $ownerInstance->getAccessibleGroups('', $this->getFieldModel()->getFieldDataType(), true);
		if ($recordModel && ($recordId = $recordModel->getId())) {
			unset($owners['LBL_GROUPS'][$recordId]);
		}
		return $owners;
	}
}

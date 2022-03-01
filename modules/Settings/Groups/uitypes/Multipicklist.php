<?php
/**
 * UIType multipicklist field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Multipicklist Field Class.
 */
class Settings_Groups_Multipicklist_UIType extends Vtiger_Multipicklist_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$fieldName = $this->getFieldModel()->getName();
		$result = [];
		$values = explode(' |##| ', $value);
		if ('modules' === $fieldName) {
			foreach ($values as $value) {
				$moduleName = \App\Module::getModuleName((int) $value);
				$displayValue = App\Language::translate($moduleName, $moduleName);
				$result[] = "<span><span class=\"modCT_{$moduleName} yfm-{$moduleName} mr-1\"></span><span>{$displayValue}</span></span>";
			}
		} elseif ('members' === $fieldName) {
			foreach ($values as $value) {
				$result[] = \App\Labels::member($value);
			}
		}

		return $rawText ? $value : implode(', ', $result);
	}

	/**
	 * Gets members list.
	 *
	 * @param Settings_Groups_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getMembersList(?Settings_Groups_Record_Model $recordModel = null): array
	{
		$members = \App\PrivilegeUtil::getMembers();
		if ($recordModel && $recordModel->getId()) {
			$type = \App\PrivilegeUtil::MEMBER_TYPE_GROUPS;
			$currentMemberId = $type . ':' . $recordModel->getId();
			unset($members[$type][$currentMemberId]);
			if (!\count($members[$type])) {
				unset($members[$type]);
			}
		}
		return $members;
	}
}

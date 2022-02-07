<?php

/**
 * Settings QuickCreateEditor module model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_QuickCreateEditor_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Update sequence number for quickcreate.
	 *
	 * @param array $blockFieldSequence
	 *
	 * @return int
	 */
	public static function updateFieldSequenceNumber($blockFieldSequence)
	{
		$db = \App\Db::getInstance();
		$result = 0;
		if ($blockFieldSequence) {
			$caseExpression = 'CASE';
			foreach ($blockFieldSequence as $newFieldSequence) {
				$caseExpression .= " WHEN fieldid = {$db->quoteValue($newFieldSequence['fieldid'])} THEN {$db->quoteValue($newFieldSequence['sequence'])}";
				$fieldIdList[] = $newFieldSequence['fieldid'];
			}
			$caseExpression .= ' END';
			$result = $db->createCommand()->update('vtiger_field', ['quickcreatesequence' => new \yii\db\Expression($caseExpression)], ['fieldid' => $fieldIdList])->execute();
			if ($result) {
				$fieldInfo = \App\Field::getFieldInfo($newFieldSequence['fieldid']);
				\App\Cache::delete('AllFieldForModule', $fieldInfo['tabid']);
			}
		}
		return $result;
	}
}

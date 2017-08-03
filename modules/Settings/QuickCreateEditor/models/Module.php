<?php

/**
 * Settings QuickCreateEditor module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_QuickCreateEditor_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Update sequence number for quickcreate
	 * @param array $blockFieldSequence
	 */
	public static function updateFieldSequenceNumber($blockFieldSequence)
	{
		$db = \App\Db::getInstance();
		$caseExpression = 'CASE';
		foreach ($blockFieldSequence as $newFieldSequence) {
			$caseExpression .= " WHEN fieldid = {$db->quoteValue($newFieldSequence['fieldid'])} THEN {$db->quoteValue($newFieldSequence['sequence'])}";
			$fieldIdList[] = $newFieldSequence['fieldid'];
		}
		$caseExpression .= ' END';
		$db->createCommand()
			->update('vtiger_field', [
				'quickcreatesequence' => new \yii\db\Expression($caseExpression),
				], ['fieldid' => $fieldIdList])->execute();
	}
}

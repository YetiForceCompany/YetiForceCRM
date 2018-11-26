<?php

namespace App\Db;

/**
 * Class that update structure and data to database.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Updater
{
	/**
	 * Function used to change picklist type field (uitype 16) to field with permissions based on role (uitype 15).
	 *
	 * $fiels = [
	 *        'fieldName',
	 *        'osstimecontrol_status',
	 * ];
	 *
	 * @param array $fiels
	 */
	public static function addRoleToPicklist($fiels)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$dbCommand = $db->createCommand();
		$roleIds = (new \App\Db\Query())->select(['roleid'])->from('vtiger_role')->column();
		$query = (new \App\Db\Query())->from('vtiger_field')
			->where(['uitype' => 16])
			->andWhere(['fieldname' => $fiels]);

		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$picklistTable = 'vtiger_' . $row['fieldname'];
			$tableSchema = $schema->getTableSchema($picklistTable);
			if ($tableSchema && !isset($tableSchema->columns['picklist_valueid'])) {
				$dbCommand->addColumn($picklistTable, 'picklist_valueid', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 10)->notNull()->defaultValue(0))->execute();
				$dbCommand->insert('vtiger_picklist', ['name' => $row['fieldname']])->execute();
				$newPicklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $row['fieldname']])->scalar();
				if (!$newPicklistId) {
					$newPicklistId = $db->getLastInsertID('vtiger_picklist_picklistid_seq');
				}
				$identifier = $row['fieldname'] . 'id';
				$query2 = (new \App\Db\Query())->select([$identifier, 'sortorderid'])->from($picklistTable);
				$dataReader2 = $query2->createCommand()->query();
				while ($picklistRow = $dataReader2->read()) {
					$newPicklistValueId = $db->getUniqueID('vtiger_picklistvalues');
					$dbCommand->update($picklistTable, ['picklist_valueid' => $newPicklistValueId], [$identifier => $picklistRow[$identifier]])->execute();
					$insertedData = [];
					foreach ($roleIds as $value) {
						$insertedData[] = [$value, $newPicklistValueId, $newPicklistId, $picklistRow['sortorderid']];
					}
					$dbCommand->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid', 'sortid'], $insertedData)->execute();
				}
				$dbCommand->update('vtiger_field', ['uitype' => 15], ['fieldid' => $row['fieldid']])->execute();
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Batch update rows.
	 *
	 * $rows = [
	 *        ['u_#__squotes_invfield', ['colspan' => 25], ['id' => 1]],
	 *    ];
	 *
	 * @param array $rows
	 */
	public static function batchUpdate($rows)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($rows as $row) {
			$dbCommand->update($row[0], $row[1], $row[2])->execute();
		}
	}

	/**
	 * Batch insert rows.
	 *
	 * $rows = [
	 *        ['vtiger_cvcolumnlist', ['cvid' => 43, 'columnindex' => 5, 'columnname' => 'cc']],
	 * ];
	 *
	 * @param array $rows
	 */
	public static function batchInsert($rows)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($rows as $row) {
			if (!isset($row[2]) || !(new \App\db\Query())->from($row[0])->where($row[2])->exists()) {
				$dbCommand->insert($row[0], $row[1])->execute();
			}
		}
	}

	/**
	 * Batch insert rows.
	 *
	 * $rows = [
	 *        ['vtiger_cvcolumnlist', ['cvid' => 43]],
	 * ];
	 *
	 * @param array $rows
	 */
	public static function batchDelete($rows)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($rows as $row) {
			$dbCommand->delete($row[0], $row[1])->execute();
		}
	}

	/**
	 * Function to add and remove cron.
	 *
	 * $crons = [
	 *        ['type' => 'add', 'data' => ['LBL_BROWSING_HISTORY', 'cron/BrowsingHistory.php', 86400, NULL, NULL, 1, NULL, 29, NULL]],
	 *        ['type' => 'remove', 'data' => ['LBL_BATCH_PROCESSES']],
	 * ];
	 *
	 * @param array $crons
	 */
	public static function cron($crons)
	{
		if (!$crons) {
			return [];
		}
		\App\Log::trace('Entering ' . __METHOD__);
		$cronAction = [];
		foreach ($crons as $cron) {
			if (empty($cron)) {
				continue;
			}
			$cronData = $cron['data'];
			$isExists = (new \App\Db\Query())->from('vtiger_cron_task')->where(['name' => $cronData[0], 'handler_file' => $cronData[1]])->exists();
			if (!$isExists && $cron['type'] === 'add') {
				\vtlib\Cron::register($cronData[0], $cronData[1], $cronData[2], $cronData[6], $cronData[5], 0, $cronData[8]);
				$cronAction[] = $cronData[0];
			} elseif ($isExists && $cron['type'] === 'remove') {
				\vtlib\Cron::deregister($cronData[0]);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);

		return $cronAction;
	}
}

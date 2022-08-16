<?php
/**
 * File that update structure and data to database.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Db;

/**
 * Class that update structure and data to database.
 */
class Updater
{
	/**
	 * Function used to change picklist type field (uitype 16) to field with permissions based on role (uitype 15).
	 *
	 * $fields = [
	 *     'fieldName',
	 *     'osstimecontrol_status',
	 * ];
	 *
	 * @param array $fields
	 */
	public static function addRoleToPicklist($fields): void
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$dbCommand = $db->createCommand();
		$roleIds = (new \App\Db\Query())->select(['roleid'])->from('vtiger_role')->column();
		$query = (new \App\Db\Query())->from('vtiger_field')
			->where(['uitype' => 16])
			->andWhere(['fieldname' => $fields]);

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
	 * Function used to change picklist type field (uitype 15 to 16).
	 *
	 * @param string[] $fields List of field names
	 */
	public static function removeRoleToPicklist($fields): void
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$dbCommand = $db->createCommand();

		$query = (new \App\Db\Query())->from('vtiger_field')->where(['uitype' => 16])->andWhere(['fieldname' => $fields]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$picklistTable = 'vtiger_' . $row['fieldname'];
			$tableSchema = $schema->getTableSchema($picklistTable);
			if ($tableSchema && isset($tableSchema->columns['picklist_valueid'])) {
				$dbCommand->update('vtiger_field', ['uitype' => 15], ['fieldid' => $row['fieldid']])->execute();
				$dbCommand->dropColumn($picklistTable, 'picklist_valueid')->execute();
				$picklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $row['fieldname']])->scalar();
				if ($picklistId) {
					$dbCommand->delete('vtiger_picklist', ['name' => $row['fieldname']])->execute();
					$dbCommand->delete('vtiger_role2picklist', ['picklistid' => $picklistId])->execute();
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Batch update rows.
	 *
	 * $rows = [
	 *	  ['table name', [ update ], [ condition ],
	 *    ['u_#__squotes_invfield', ['colspan' => 25], ['id' => 1]],
	 * ];
	 *
	 * @param array $rows
	 *
	 * @throws \App\Exceptions\DbException
	 *
	 * @return int[]
	 */
	public static function batchUpdate($rows): array
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$s = 0;
		foreach ($rows as $row) {
			try {
				$s += $dbCommand->update($row[0], $row[1], $row[2] ?? '')->execute();
			} catch (\Throwable $th) {
				throw new \App\Exceptions\DbException(\App\Utils::varExport(['tableName' => $row[0], 'columns' => $row[1], 'conditions' => $row[2] ?? null]) . PHP_EOL . $th->__toString(), $th->getCode());
			}
		}
		return ['affected' => $s, 'all' => \count($rows)];
	}

	/**
	 * Batch insert rows.
	 *
	 * $rows = [
	 *    ['vtiger_cvcolumnlist', ['cvid' => 43, 'columnindex' => 5, 'columnname' => 'cc'], ],
	 * ];
	 *
	 * @param array $rows
	 *
	 * @throws \App\Exceptions\DbException
	 *
	 * @return int[]
	 */
	public static function batchInsert($rows): array
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$s = 0;
		foreach ($rows as $row) {
			try {
				if (!isset($row[2]) || !(new \App\db\Query())->from($row[0])->where($row[2])->exists()) {
					$dbCommand->insert($row[0], $row[1])->execute();
					++$s;
				}
			} catch (\Throwable $th) {
				throw new \App\Exceptions\DbException(\App\Utils::varExport(['tableName' => $row[0], 'columns' => $row[1], 'conditions' => $row[2] ?? null]) . PHP_EOL . $th->__toString(), $th->getCode());
			}
		}
		return ['affected' => $s, 'all' => \count($rows)];
	}

	/**
	 * Batch insert rows.
	 *
	 * $rows = [
	 *     ['vtiger_cvcolumnlist', ['cvid' => 43]],
	 * ];
	 *
	 * @param array $rows
	 *
	 * @throws \App\Exceptions\DbException
	 *
	 * @return int[]
	 */
	public static function batchDelete($rows): array
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$s = 0;
		foreach ($rows as $row) {
			try {
				$s += $dbCommand->delete($row[0], $row[1])->execute();
			} catch (\Throwable $th) {
				throw new \App\Exceptions\DbException(\App\Utils::varExport(['tableName' => $row[0], 'conditions' => $row[1] ?? null]) . PHP_EOL . $th->__toString(), $th->getCode());
			}
		}
		return ['affected' => $s, 'all' => \count($rows)];
	}

	/**
	 * Function to add and remove cron.
	 *
	 * $crons = [
	 *     ['type' => 'add', 'data' => ['LBL_BROWSING_HISTORY', 'cron/BrowsingHistory.php', 86400, NULL, NULL, 1, NULL, 29, NULL]],
	 *     ['type' => 'remove', 'data' => ['LBL_BATCH_PROCESSES']],
	 * ];
	 *
	 * @param string[] $crons
	 */
	public static function cron($crons): array
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
			$isExists = (new \App\Db\Query())->from('vtiger_cron_task')->where(['name' => $cronData[0], 'handler_class' => $cronData[1]])->exists();
			if (!$isExists && 'add' === $cron['type']) {
				\vtlib\Cron::register($cronData[0], $cronData[1], $cronData[2], $cronData[6], $cronData[5], 0, $cronData[8]);
				$cronAction[] = $cronData[0];
			} elseif ($isExists && 'remove' === $cron['type']) {
				\vtlib\Cron::deregister($cronData[0]);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
		return $cronAction;
	}
}

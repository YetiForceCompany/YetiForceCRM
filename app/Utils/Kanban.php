<?php
/**
 * Kanban utils file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Utils;

/**
 * Kanban utils class.
 */
class Kanban
{
	/**
	 * Get boards by module name.
	 *
	 * @param string $moduleName
	 * @param bool   $privileges
	 *
	 * @return array
	 */
	public static function getBoards(string $moduleName, bool $privileges = false): array
	{
		if (!\App\Cache::has('KanbanGetBoards', $moduleName)) {
			$dataReader = (new \App\Db\Query())->from('s_#__kanban_boards')
				->where(['tabid' => \App\Module::getModuleId($moduleName)])
				->orderBy(['sequence' => SORT_ASC])
				->createCommand(\App\Db::getInstance('admin'))->query();
			$rows = [];
			while ($row = $dataReader->read()) {
				$row['detail_fields'] = \App\Json::decode($row['detail_fields']);
				$row['sum_fields'] = \App\Json::decode($row['sum_fields']);
				$rows[$row['fieldid']] = $row;
			}
			\App\Cache::save('KanbanGetBoards', $moduleName, $rows);
		} else {
			$rows = \App\Cache::get('KanbanGetBoards', $moduleName);
		}
		if ($privileges) {
			foreach ($rows as $id => $row) {
				$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($id);
				if (!$fieldModel->isAjaxEditable()) {
					unset($rows[$id]);
				}
			}
		}
		return $rows;
	}

	/**
	 * Get board by id.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getBoard(int $id): array
	{
		if ($row = (new \App\Db\Query())->from('s_#__kanban_boards')->where(['id' => $id])->one(\App\Db::getInstance('admin'))) {
			foreach (['detail_fields', 'sum_fields'] as $type) {
				$row[$type] = \App\Json::decode($row[$type]);
			}
		}
		return $row ?: [];
	}

	/**
	 * Add boards.
	 *
	 * @param int $fieldId
	 *
	 * @return void
	 */
	public static function addBoard(int $fieldId): void
	{
		$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
		$sequence = (new \App\Db\Query())
			->from('s_#__kanban_boards')
			->where(['tabid' => $fieldModel->getModuleId()])
			->max('sequence') ?? 0;
		$fields = ['assigned_user_id'];
		$fields[] = $fieldModel->getModule()->getAlphabetSearchField();
		\App\Db::getInstance('admin')->createCommand()
			->insert('s_#__kanban_boards', [
				'tabid' => $fieldModel->getModuleId(),
				'fieldid' => $fieldId,
				'detail_fields' => \App\Json::encode($fields),
				'sum_fields' => '[]',
				'sequence' => $sequence + 1,
			])->execute();

		self::clearCache($fieldModel->getModuleName());
	}

	/**
	 * Update boards.
	 *
	 * @param int    $id
	 * @param string $type
	 * @param array  $value
	 *
	 * @return void
	 */
	public static function updateBoard(int $id, string $type, array $value): void
	{
		$row = self::getBoard($id);
		\App\Db::getInstance('admin')->createCommand()
			->update('s_#__kanban_boards', [$type => \App\Json::encode($value)], ['id' => $id])
			->execute();
		self::clearCache(\App\Module::getModuleName($row['tabid']));
	}

	/**
	 * Update boards sequence boards.
	 *
	 * @param string $moduleName
	 * @param array  $rows
	 *
	 * @return void
	 */
	public static function updateSequence(string $moduleName, array $rows): void
	{
		$createCommand = \App\Db::getInstance('admin')->createCommand();
		foreach ($rows as $seq => $id) {
			$createCommand->update('s_#__kanban_boards', ['sequence' => $seq], ['id' => $id])->execute();
		}
		self::clearCache($moduleName);
	}

	/**
	 * Delete boards.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public static function deleteBoard(int $id): void
	{
		if ($row = self::getBoard($id)) {
			\App\Db::getInstance('admin')->createCommand()
				->delete('s_#__kanban_boards', ['id' => $id])
				->execute();

			self::clearCache(\App\Module::getModuleName($row['tabid']));
		}
	}

	/**
	 * Get supported fields.
	 *
	 * @param string $moduleName
	 *
	 *  @return \Vtiger_Field_Model[]
	 */
	public static function getSupportedFields(string $moduleName): array
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$fields = [];
		foreach ($moduleModel->getFieldsByType(['picklist', 'owner'], true) as $fieldModel) {
			if ($fieldModel->isAjaxEditable()) {
				$fields[$fieldModel->getId()] = $fieldModel;
			}
		}
		return $fields;
	}

	/**
	 * Clear cache for module.
	 *
	 * @param string $moduleName
	 */
	public static function clearCache(string $moduleName)
	{
		\App\Cache::delete('KanbanGetBoards', $moduleName);
	}

	/**
	 * Remove field from kanban board.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 */
	public static function deleteField(string $moduleName, string $fieldName)
	{
		foreach (self::getBoards($moduleName) as $board) {
			foreach (['detail_fields', 'sum_fields'] as $type) {
				if (false !== ($key = array_search($fieldName, $board[$type]))) {
					unset($board[$type][$key]);
					self::updateBoard($board['id'], $type, $board[$type]);
				}
			}
		}
	}
}

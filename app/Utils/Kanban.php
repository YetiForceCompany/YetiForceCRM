<?php
/**
 * Kanban utils file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		if (\App\Cache::has('KanbanGetBoards', $moduleName)) {
			return \App\Cache::get('KanbanGetBoards', $moduleName);
		}
		$dataReader = (new \App\Db\Query())->from('s_#__kanban_boards')
			->where(['tabid' => \App\Module::getModuleId($moduleName)])
			->orderBy(['sequence' => SORT_ASC])
			->createCommand(\App\Db::getInstance('admin'))->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$row['detail_fields'] = \App\Json::decode($row['detail_fields']);
			$row['sum_fields'] = \App\Json::decode($row['sum_fields']);
			$rows[$row['fieldid']] = $row;
			\App\Cache::save('KanbanGetBoardById', $row['id'], $row);
		}
		\App\Cache::save('KanbanGetBoards', $moduleName, $rows);
		if ($privileges) {
			foreach ($rows as $id => $row) {
				$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($row['fieldid']);
				if (!$fieldModel->isEditable()) {
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
		if (\App\Cache::has('KanbanGetBoardById', $id)) {
			return \App\Cache::get('KanbanGetBoardById', $id);
		}
		$row = (new \App\Db\Query())->from('s_#__kanban_boards')
			->where(['id' => $id])->one(\App\Db::getInstance('admin'));
		$row['detail_fields'] = \App\Json::decode($row['detail_fields']);
		$row['sum_fields'] = \App\Json::decode($row['sum_fields']);
		return \App\Cache::save('KanbanGetBoardById', $id, $row);
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
		$moduleName = $fieldModel->getModuleName();
		$sequence = (new \App\Db\Query())
			->from('s_#__kanban_boards')
			->where(['tabid' => $fieldModel->getModuleId()])
			->max('sequence') ?? 0;
		$fields = ['assigned_user_id'];
		$fields[] = \CRMEntity::getInstance($moduleName)->def_basicsearch_col;
		\App\Db::getInstance('admin')->createCommand()
			->insert('s_#__kanban_boards', [
				'tabid' => $fieldModel->getModuleId(),
				'fieldid' => $fieldId,
				'detail_fields' => \App\Json::encode($fields),
				'sum_fields' => '[]',
				'sequence' => $sequence + 1,
			])->execute();

		\App\Cache::delete('KanbanGetBoards', $moduleName);
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
		\App\Cache::delete('KanbanGetBoardById', $id);
		\App\Cache::delete('KanbanGetBoards', \App\Module::getModuleName($row['tabid']));
	}

	/**
	 * Update boards sequence boards.
	 *
	 * @param array $seq
	 * @param array $rows
	 *
	 * @return void
	 */
	public static function updateSequence(array $rows): void
	{
		$createCommand = \App\Db::getInstance('admin')->createCommand();
		foreach ($rows as $seq => $id) {
			$createCommand->update('s_#__kanban_boards', ['sequence' => $seq], ['id' => $id])->execute();
			\App\Cache::delete('KanbanGetBoardById', $id);
		}
		$row = self::getBoard($id);
		\App\Cache::delete('KanbanGetBoards', \App\Module::getModuleName($row['tabid']));
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
		$row = self::getBoard($id);
		\App\Db::getInstance('admin')->createCommand()
			->delete('s_#__kanban_boards', ['id' => $id])
			->execute();
		\App\Cache::delete('KanbanGetBoardById', $id);
		\App\Cache::delete('KanbanGetBoards', \App\Module::getModuleName($row['tabid']));
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
		foreach ($moduleModel->getFields() as $fieldModel) {
			if (\in_array($fieldModel->getFieldDataType(), ['picklist', 'owner']) && !isset($fields[$fieldModel->getId()])) {
				$fields[$fieldModel->getId()] = $fieldModel;
			}
		}
		return $fields;
	}
}

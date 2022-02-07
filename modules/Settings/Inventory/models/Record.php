<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_Record_Model extends \App\Base
{
	private $type;

	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		return $this->get('name');
	}

	public function getValue()
	{
		return CurrencyField::convertToUserFormat($this->get('value'), null, true);
	}

	public function getStatus()
	{
		return $this->get('status');
	}

	/**
	 * Is default tax value for group.
	 *
	 * @return int
	 */
	public function getDefault()
	{
		return $this->get('default');
	}

	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getCreateUrl()
	{
		return '?module=Inventory&parent=Settings&view=ModalAjax&type=' . $this->getType();
	}

	public function getEditUrl()
	{
		return '?module=Inventory&parent=Settings&view=ModalAjax&type=' . $this->getType() . '&id=' . $this->getId();
	}

	private static $tableName = ['CreditLimits' => 'a_#__inventory_limits', 'Taxes' => 'a_#__taxes_global', 'Discounts' => 'a_#__discounts_global'];

	public static function getTableNameFromType($type)
	{
		return static::$tableName[$type];
	}

	/**
	 * Function clears cache.
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		\App\Cache::delete('Inventory', $this->getType());
	}

	public function save()
	{
		$table = self::getTableNameFromType($this->getType());
		$id = $this->getId();
		if (!empty($id) && $table) {
			$updateRows = [
				'name' => $this->getName(),
				'value' => $this->get('value'),
				'status' => $this->get('status'),
			];
			if ('Taxes' === $this->getType()) {
				if ($this->get('default')) {
					$this->disableDefaultsTax();
				}
				$updateRows['default'] = $this->get('default');
			}
			\App\Db::getInstance()->createCommand()
				->update($table, $updateRows, ['id' => $id])
				->execute();
		} else {
			$id = $this->add();
		}
		$this->clearCache();

		return $id;
	}

	/**    Function used to add the tax type which will do database alterations.
	 * @param string $taxlabel - tax label name to be added
	 * @param string $taxvalue - tax value to be added
	 * @param string $sh       - sh or empty , if sh passed then the tax will be added in shipping and handling related table
	 */
	public function add()
	{
		$table = self::getTableNameFromType($this->getType());
		if ($table) {
			$insertData = [
				'status' => $this->get('status'),
				'value' => $this->get('value'),
				'name' => $this->getName(),
			];

			if ('Taxes' === $this->getType()) {
				if ($this->get('default')) {
					$this->disableDefaultsTax();
				}
				$insertData['default'] = $this->get('default');
			}
			$db = \App\Db::getInstance();
			$db->createCommand()
				->insert($table, $insertData)->execute();
			return $db->getLastInsertID($table . '_id_seq');
		}
		throw new Error('Error occurred while adding value');
	}

	/**
	 * Function used to remove all defaults tax settings.
	 */
	public function disableDefaultsTax()
	{
		$table = self::getTableNameFromType($this->getType());
		if ($table) {
			\App\Db::getInstance()->createCommand()
				->update($table, [
					'default' => 0,
				])
				->execute();
		}
		$this->clearCache();
	}

	public function delete()
	{
		$table = self::getTableNameFromType($this->getType());
		if ($table) {
			\App\Db::getInstance()->createCommand()
				->delete($table, ['id' => $this->getId()])
				->execute();
			$this->clearCache();

			return true;
		}
		throw new Error('Error occurred while deleting value');
	}

	public static function getDataAll($type)
	{
		$recordList = [];
		$table = self::getTableNameFromType($type);
		if (!$table) {
			return $recordList;
		}
		$dataReader = (new \App\Db\Query())->from($table)->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordModel = new self();
			$recordModel->setData($row)->setType($type);
			$recordList[] = $recordModel;
		}
		$dataReader->close();

		return $recordList;
	}

	public static function getInstanceById($id, $type = '')
	{
		$table = self::getTableNameFromType($type);
		if (!$table) {
			return false;
		}
		$row = (new \App\Db\Query())->from($table)
			->where(['id' => $id])
			->createCommand()->queryOne();
		$recordModel = new self();
		if (false !== $row) {
			$recordModel->setData($row)->setType($type);
		}
		return $recordModel;
	}

	public static function checkDuplicate($label, $excludedIds = [], $type = '')
	{
		if (!\is_array($excludedIds)) {
			if (!empty($excludedIds)) {
				$excludedIds = [$excludedIds];
			} else {
				$excludedIds = [];
			}
		}
		$table = self::getTableNameFromType($type);
		$query = (new \App\Db\Query())
			->from($table)
			->where(['name' => $label]);
		if (!empty($excludedIds)) {
			$query->andWhere(['NOT IN', 'id', $excludedIds]);
		}
		return ($query->count() > 0) ? true : false;
	}
}

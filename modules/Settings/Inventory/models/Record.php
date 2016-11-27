<?php

/**
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_Record_Model extends Vtiger_Base_Model
{

	public function __construct($values = [])
	{
		parent::__construct($values);
	}

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
		return $this->get('value');
	}

	public function getStatus()
	{
		return $this->get('status');
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
	 * Function clears cache
	 */
	public function clearCache()
	{
		\App\Cache::delete('Inventory', $this->getType());
	}

	public function save()
	{
		$tablename = self::getTableNameFromType($this->getType());
		$id = $this->getId();
		if (!empty($id) && $tablename) {
			\App\Db::getInstance()->createCommand()
				->update($tablename, [
					'name' => $this->getName(),
					'value' => $this->get('value'),
					'status' => $this->get('status')
					], ['id' => $id])
				->execute();
		} else {
			$id = $this->add();
		}
		$this->clearCache();
		return $id;
	}

	/** 	Function used to add the tax type which will do database alterations
	 * 	@param string $taxlabel - tax label name to be added
	 * 	@param string $taxvalue - tax value to be added
	 *      @param string $sh - sh or empty , if sh passed then the tax will be added in shipping and handling related table
	 *      @return void
	 */
	public function add()
	{
		$tableName = self::getTableNameFromType($this->getType());
		if ($tableName) {
			$db = \App\Db::getInstance();
			$db->createCommand()
				->insert($tableName, [
					'status' => $this->get('status'),
					'value' => $this->get('value'),
					'name' => $this->getName()
				])->execute();
			return $db->getLastInsertID($tableName . '_id_seq');
		}
		throw new Error('Error occurred while adding value');
	}

	public function delete()
	{
		$tableName = self::getTableNameFromType($this->getType());
		if ($tableName) {
			\App\Db::getInstance()->createCommand()
				->delete($tableName, ['id' => $this->getId()])
				->execute();
			$this->clearCache();
			return true;
		}
		throw new Error('Error occurred while deleting value');
	}

	public static function getDataAll($type)
	{
		$recordList = [];
		$tableName = self::getTableNameFromType($type);
		if (!$tableName) {
			return $recordList;
		}
		$dataReader = (new \App\Db\Query)->from($tableName)->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordModel = new self();
			$recordModel->setData($row)->setType($type);
			$recordList[] = $recordModel;
		}
		return $recordList;
	}

	public static function getInstanceById($id, $type = '')
	{
		$tableName = self::getTableNameFromType($type);
		if (!$tableName) {
			return false;
		}
		$row = (new \App\Db\Query())->from($tableName)
				->where(['id' => $id])
				->createCommand()->queryOne();
		$recordModel = new self();
		if ($row !== false) {
			$recordModel->setData($row)->setType($type);
		}
		return $recordModel;
	}

	public static function checkDuplicate($label, $excludedIds = [], $type = '')
	{
		if (!is_array($excludedIds)) {
			if (!empty($excludedIds)) {
				$excludedIds = [$excludedIds];
			} else {
				$excludedIds = [];
			}
		}
		$tableName = self::getTableNameFromType($type);
		$query = (new \App\Db\Query())
			->from($tableName)
			->where(['name' => $label]);
		if (!empty($excludedIds)) {
			$query->andWhere(['NOT IN', 'id', $excludedIds]);
		}
		return ($query->count() > 0) ? true : false;
	}
}

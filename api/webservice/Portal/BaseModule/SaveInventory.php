<?php
/**
 * The file contains a the SaveInventory class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Saving data to the inventory module.
 */
class SaveInventory extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['PUT', 'POST'];

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function put(): array
	{
		return $this->post();
	}

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function post(): array
	{
		$moduleName = $this->controller->request->getModule();
		$inventory = $this->controller->request->get('inventory');
		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set('subject', $moduleName . '/' . date('Y-m-d'));
		$recordModel->initInventoryData(
			$this->createClass($moduleName, $inventory)->getInventoryData(),
			false
		);
		$recordModel->save();
		return [
			'id' => $recordModel->getId(),
		];
	}

	/**
	 * Create class.
	 *
	 * @param string $moduleName
	 *
	 * @return \Api\Portal\BaseModel\AbstractSaveInventory
	 */
	protected function createClass(string $moduleName, array $inventory): \Api\Portal\BaseModel\AbstractSaveInventory
	{
		$className = "Api\\Portal\\{$moduleName}Model\\SaveInventory";
		if (class_exists($className)) {
			return new $className($moduleName, $inventory);
		}
		return new \Api\Portal\BaseModel\SaveInventory($moduleName, $inventory);
	}
}

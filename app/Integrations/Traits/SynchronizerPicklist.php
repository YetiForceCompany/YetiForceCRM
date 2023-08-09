<?php
/**
 * Synchronizer trait file for picklist.
 *
 * @package Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Traits;

/**
 * Synchronizer trait for picklist.
 */
trait SynchronizerPicklist
{
	/** @var int[] */
	private $roleIdList = [];

	/**
	 * Import account type from API.
	 *
	 * @return void
	 */
	public function import(): void
	{
		if ($this->config->get('log_all')) {
			$this->controller->log('Start import ' . $this->name, []);
		}
		$isRoleBased = $this->fieldModel->isRoleBased();
		$values = $this->getPicklistValues();
		$i = 0;
		foreach ($this->cache as $key => $value) {
			if (empty($value)) {
				continue;
			}
			$name = mb_strtolower($value);
			if (empty($values[$name])) {
				try {
					$itemModel = $this->fieldModel->getItemModel();
					$itemModel->validateValue('name', $value);
					$itemModel->set('name', $value);
					if ($isRoleBased) {
						if (empty($this->roleIdList)) {
							$this->roleIdList = array_keys(\Settings_Roles_Record_Model::getAll());
						}
						$itemModel->set('roles', $this->roleIdList);
					}
					$itemModel->save();
					$this->cacheList[$value] = $key;
					++$i;
				} catch (\Throwable $ex) {
					$this->logError('import ' . $this->name, ['API' => $value], $ex);
				}
			} else {
				$this->cacheList[$values[$name]] = $key;
			}
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End import ' . $this->name, ['imported' => $i]);
		}
	}
}

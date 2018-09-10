<?php

namespace Api\Portal\BaseModule;

/**
 * Records hierarchy action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Hierarchy extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/** @var int Pecursion limit */
	public $limit = 100;

	/** @var string Module name */
	public $moduleName;

	/** @var bool|int Search id in the hierarchy */
	public $findId = false;
	public $mainField;
	public $childField;
	public $records = [];
	public $recursion = [];

	/**
	 * Check permission to method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		$return = parent::checkPermission();
		if ($this->getPermissionType() === 1) {
			throw new \Api\Core\Exception('Not available for this type of user', 405);
		}
		$this->moduleName = $this->controller->request->get('module');

		return $return;
	}

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$parentCrmId = $this->getParentCrmId();
		if ($this->getPermissionType() > 2) {
			$fields = \App\Field::getRelatedFieldForModule($this->moduleName);
			if (!isset($fields[$this->moduleName])) {
				throw new \Api\Core\Exception('No hierarchy', 405);
			}
			$field = $fields[$this->moduleName];
			$entityFieldInfo = \App\Module::getEntityInfo($this->moduleName);
			$queryGenerator = new \App\QueryGenerator($this->moduleName);
			$this->mainFieldName = $entityFieldInfo['fieldname'];
			$this->childField = $field['fieldname'];
			$this->childColumn = "{$field['tablename']}.{$field['columnname']}";
			$queryGenerator->setFields(['id', $this->childField, $this->mainFieldName]);
			$this->getRecords($queryGenerator, $parentCrmId);
		}
		if (!isset($this->records[$parentCrmId])) {
			$this->records[$parentCrmId] = [
				'id' => $parentCrmId,
				'name' => \App\Record::getLabel($parentCrmId),
			];
		}
		return $this->records;
	}

	/**
	 * Get records in hierarchy.
	 *
	 * @param \App\QueryGenerator $mainQueryGenerator
	 * @param int                 $parentId
	 * @param string              $type
	 *
	 * @return bool
	 */
	public function getRecords(\App\QueryGenerator $mainQueryGenerator, $parentId, $type = 'child')
	{
		if ($this->limit === 0 || isset($this->recursion[$parentId][$type])) {
			return false;
		}
		--$this->limit;
		$queryGenerator = clone $mainQueryGenerator;
		if ($type === 'parent') {
			$queryGenerator->addCondition('id', $parentId, 'e');
		} else {
			$queryGenerator->addNativeCondition([$this->childColumn => $parentId]);
		}
		$this->recursion[$parentId][$type] = true;
		foreach ($queryGenerator->createQuery()->all() as $row) {
			$id = $row['id'];
			if (isset($this->records[$id])) {
				continue;
			}
			$this->records[$id] = [
				'id' => $id,
				'parent' => $row[$this->childField],
				'name' => $row[$this->mainFieldName],
			];
			if ($this->findId && $this->findId === $id) {
				$this->limit = 0;

				return true;
			}
			if (!empty($row[$this->childField])) {
				if ($this->getPermissionType() === 4) {
					$this->getRecords(clone $mainQueryGenerator, $row[$this->childField], 'parent');
					$this->getRecords(clone $mainQueryGenerator, $id, 'parent');
				} elseif ($this->getPermissionType() === 3) {
					$this->getRecords(clone $mainQueryGenerator, $row[$this->childField], 'child');
					$this->getRecords(clone $mainQueryGenerator, $id, 'child');
				}
			}
		}
	}
}

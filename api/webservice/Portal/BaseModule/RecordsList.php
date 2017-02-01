<?php
namespace Api\Portal\BaseModule;

/**
 * Get record list class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RecordsList extends \Api\Core\BaseAction
{

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$records = $headers = [];
		$queryGenerator = $this->getQuery();
		$fieldsModel = $queryGenerator->getListViewFields();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$record = ['recordLabel' => \App\Record::getLabel($row['id'])];
			foreach ($fieldsModel as $fieldName => &$fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
				}
			}
			$records[$row['id']] = $record;
		}
		foreach ($fieldsModel as $fieldName => &$fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
		}
		return [
			'headers' => $headers,
			'records' => $records,
			'count' => count($records)
		];
	}

	/**
	 * Get query record list
	 * @return \App\QueryGenerator
	 * @throws \Api\Core\Exception
	 */
	public function getQuery()
	{
		$queryGenerator = new \App\QueryGenerator($this->controller->request->get('module'));
		$queryGenerator->initForDefaultCustomView();
		if ($this->getPermissionType() !== 1) {
			$this->getQueryByParentRecord($queryGenerator);
		}
		return $queryGenerator;
	}

	/**
	 * Get query by parent record
	 * @param \App\QueryGenerator $queryGenerator
	 * @throws \Api\Core\Exception
	 */
	public function getQueryByParentRecord(\App\QueryGenerator $queryGenerator)
	{
		$parentId = $this->getParentCrmId();
		$parentModule = \App\Record::getType($parentId);
		$fields = \App\Field::getReletedFieldForModule($queryGenerator->getModule());
		$foundField = true;
		if (\App\ModuleHierarchy::getModuleLevel($queryGenerator->getModule()) === 0) {
			$queryGenerator->addCondition('id', $parentId, 'e');
		} elseif (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			$queryGenerator->addNativeCondition(["{$field['tablename']}.{$field['columnname']}" => $parentId]);
		} elseif ($fields) {
			$foundField = false;
			foreach ($fields as $moduleName => $field) {
				if ($moduleName === $parentModule) {
					continue;
				}
				if ($reletedField = \App\Field::getReletedFieldForModule($moduleName, $parentModule)) {
					$queryGenerator->addReletedCondition([
						'sourceField' => $field['fieldname'],
						'relatedModule' => $moduleName,
						'relatedField' => $reletedField['fieldname'],
						'value' => $parentId,
						'operator' => 'e',
						'conditionGroup' => true
					]);
					$foundField = true;
				}
			}
		}
		if (!$foundField) {
			throw new \Api\Core\Exception('Invalid module, no relationship', 400);
		}
	}
}

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
		//$moduleName = 'SSalesProcesses';
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->initForDefaultCustomView();
		$records = [];
		$fieldsModel = $queryGenerator->getListViewFields();
		$this->getQueryPermissions($queryGenerator);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$record = [];
			foreach ($fieldsModel as $fieldName => $fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
				}
			}
			$records[$row['id']] = $record;
			//var_dump($row['id']);
		}
		$headers = [];
		foreach ($fieldsModel as $fieldName => $fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
		}
		return [
			'headers' => $headers,
			'records' => $records,
			'count' => count($records)
		];
	}

	public function getQueryPermissions(\App\QueryGenerator $queryGenerator)
	{
		//$queryGenerator->permissions = false;
	}
}

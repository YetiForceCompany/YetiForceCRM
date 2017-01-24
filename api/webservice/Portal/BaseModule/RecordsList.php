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

	protected $requestMethod = ['GET'];

	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->initForDefaultCustomView();
		$records = [];
		$fieldsModel = $queryGenerator->getListViewFields();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$record = [];
			foreach ($fieldsModel as $fieldName => $fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
				}
			}
			$records[$row['id']] = $record;
		}
		$headers = [];
		foreach ($fieldsModel as $fieldName => $fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
		}
		return ['headers' => $headers, 'records' => $records, 'count' => 456];
	}
}

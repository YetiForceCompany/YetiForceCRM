<?php

/**
 * Supplies Record Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Record_Model extends Vtiger_Record_Model
{

	protected $jsonFields = ['discountparam','taxparam'];

	/**
	 * Save supplie data
	 * @param Vtiger_Request $request 
	 */
	public function saveSupplieData(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__);

		$moduleName = $request->getModule();
		$SupFieldModel = Supplies_SupField_Model::getCleanInstance();
		$fields = $SupFieldModel->getColumns($moduleName);
		$table = $SupFieldModel->getTableName($moduleName, 'data');
		$numRow = $request->get('suppliesRowNo');

		$db->pquery("delete from $table where id = ? ", [$this->getId()]);

		for ($i = 1; $i <= $numRow; $i++) {
			if (!$request->has(reset($fields) . $i)) {
				continue;
			}
			$insertData = ['id' => $this->getId(), 'seq' => $request->get('seq' . $i)];
			foreach ($fields as $field) {
				$insertData[$field] = $this->getValueForSave($request, $field, $i);
			}
			$db->insert($table, $insertData);
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
	}

	public function getValueForSave(Vtiger_Request $request, $field, $i)
	{
		$value = '';
		if (in_array($field, $this->jsonFields) && $request->get($field . $i) != '') {
			$value = json_encode($request->get($field . $i));
		} else if ($request->has($field . $i)) {
			$value = $request->get($field . $i);
		} else if ($request->has($field)) {
			$value = $request->get($field);
		}
		return $value;
	}

	/**
	 * Loading the Supplies data for a record
	 * @return array Supplies data
	 */
	public function getSupplieData()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| module:' . $module . ' record:' . $record);

		$module = $this->getModuleName();
		$record = $this->getId();
		if (empty($record)) {
			return [];
		}

		$db = PearDatabase::getInstance();
		$sups = Supplies_SupField_Model::getTableName($module, 'data');
		$result = $db->pquery('SELECT * FROM ' . $sups . ' WHERE id = ? ORDER BY seq', [$record]);
		$fields = [];
		while ($row = $db->fetch_array($result)) {
			$fields[] = $row;
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $fields;
	}
}
